#!/usr/bin/env python3
"""Patch production .env: SHARED_CDN_URI + HAPP_SUBSCRIPTION_ANNOUNCE."""
from pathlib import Path

ENV = Path("/var/www/testv/.env")
UPDATES = {
    "SHARED_CDN_URI": (
        "vless://38aaafc8-d95e-438a-9535-d02147417200@cdn.avavpn.ru:443"
        "?encryption=none&security=tls&sni=avavpn.ru&host=cdn.avavpn.ru"
        "&type=xhttp&path=%2Fapi%2Fv1%2Fupload%2F&mode=packet-up"
        "&extra=%7B%22uplinkHTTPMethod%22%3A%22GET%22%2C%22xPaddingBytes%22%3A%22100-1000%22%7D"
        "#CDN-obhod"
    ),
    "HAPP_SUBSCRIPTION_ANNOUNCE": (
        "Сервера восстановлены, обновите подписку. "
        "Все операторы работают на «Обход блокировок»"
    ),
}

lines = ENV.read_text(encoding="utf-8").splitlines()
seen: set[str] = set()
out: list[str] = []

for line in lines:
    if "=" in line and not line.lstrip().startswith("#"):
        key = line.split("=", 1)[0].strip()
        if key in UPDATES:
            if key not in seen:
                out.append(f'{key}="{UPDATES[key]}"')
                seen.add(key)
            continue
    out.append(line)

for key, val in UPDATES.items():
    if key not in seen:
        out.append(f'{key}="{val}"')

ENV.write_text("\n".join(out) + "\n", encoding="utf-8")
print("patched", ", ".join(UPDATES))
