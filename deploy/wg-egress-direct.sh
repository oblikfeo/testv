#!/usr/bin/env bash
# Прямой WireGuard RU hub → France (без wstunnel).
# Трафик «Мобильная сеть» (xray sendThrough 10.10.10.2) идёт в table 200 → wg0 → 82.22.50.114.
#
# Требует готовых ключей в /etc/wireguard/wg0.conf (PrivateKey, Peer PublicKey).
# Запуск на RU hub (195.133.198.70): bash deploy/wg-egress-direct.sh
set -euo pipefail

FR_HOST="${FR_HOST:-82.22.50.114}"
FR_PASS="${FR_PASS:-}"
RU_SRC_IP="${RU_SRC_IP:-195.133.198.70}"
WG_CONF="/etc/wireguard/wg0.conf"

if [[ ! -f "$WG_CONF" ]]; then
  echo "Нет $WG_CONF — сначала настройте WireGuard ключи." >&2
  exit 1
fi

GW=$(ip -4 route show default | awk '/default/ {print $3; exit}')
DEV=$(ip -4 route show default | awk '/default/ {print $5; exit}')

# Сохранить PrivateKey / PublicKey из текущего конфига
PRIV=$(grep -m1 '^PrivateKey' "$WG_CONF" | cut -d= -f2- | tr -d ' ')
PUB=$(grep -m1 '^PublicKey' "$WG_CONF" | awk '/PublicKey/{print $3}')

if [[ -z "$PRIV" || -z "$PUB" ]]; then
  echo "Не найдены PrivateKey/Peer PublicKey в $WG_CONF" >&2
  exit 1
fi

cat > "$WG_CONF" <<EOF
[Interface]
Address = 10.10.10.2/24
MTU = 1280
PrivateKey = ${PRIV}
Table = off
PostUp = ip route replace ${FR_HOST}/32 via ${GW} dev ${DEV} src ${RU_SRC_IP}; ip route replace default dev wg0 table 200; ip rule add from 10.10.10.2 lookup 200 priority 50 2>/dev/null || ip rule replace from 10.10.10.2 lookup 200 priority 50; sysctl -w net.ipv4.conf.wg0.rp_filter=0; sysctl -w net.ipv4.conf.all.rp_filter=0
PostDown = ip rule del from 10.10.10.2 lookup 200 priority 50 2>/dev/null || true; ip route flush table 200 2>/dev/null || true; ip route del ${FR_HOST}/32 2>/dev/null || true

[Peer]
PublicKey = ${PUB}
Endpoint = ${FR_HOST}:51820
AllowedIPs = 0.0.0.0/0
PersistentKeepalive = 15
EOF

cat > /usr/local/sbin/wg-egress-watchdog.sh <<'EOF'
#!/usr/bin/env bash
set -euo pipefail
if ! curl -4 -s --max-time 4 --interface 10.10.10.2 http://icanhazip.com >/dev/null 2>&1; then
  systemctl restart wg-quick@wg0
fi
EOF
chmod +x /usr/local/sbin/wg-egress-watchdog.sh

systemctl stop wstunnel-wg-client 2>/dev/null || true
systemctl disable wstunnel-wg-client 2>/dev/null || true
systemctl mask wstunnel-wg-client 2>/dev/null || true
systemctl enable wg-quick@wg0
systemctl restart wg-quick@wg0
systemctl enable wg-egress-watchdog.timer 2>/dev/null || true
systemctl start wg-egress-watchdog.timer 2>/dev/null || true

if [[ -n "$FR_PASS" ]] && command -v sshpass >/dev/null 2>&1; then
  sshpass -p "$FR_PASS" ssh -o StrictHostKeyChecking=no "root@${FR_HOST}" bash -s <<'REMOTE'
systemctl stop wstunnel-wg-server 2>/dev/null || true
systemctl disable wstunnel-wg-server 2>/dev/null || true
systemctl mask wstunnel-wg-server 2>/dev/null || true
while iptables -t nat -D POSTROUTING -s 10.10.10.0/24 -o ens1 -j MASQUERADE 2>/dev/null; do :; done
while iptables -t mangle -D FORWARD -p tcp -m tcp --tcp-flags SYN,RST SYN -j TCPMSS --clamp-mss-to-pmtu 2>/dev/null; do :; done
systemctl restart wg-quick@wg0 2>/dev/null || wg-quick up wg0
REMOTE
fi

echo "OK: direct WG ${RU_SRC_IP} → ${FR_HOST}:51820"
wg show wg0
curl -4 -s --max-time 8 --interface 10.10.10.2 http://icanhazip.com || echo "(egress check failed — watchdog перезапустит wg)"
