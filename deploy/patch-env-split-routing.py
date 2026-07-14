#!/usr/bin/env python3
"""Apply split Happ routing env vars on hub."""
from pathlib import Path

ENV = Path("/var/www/testv/.env")
UPDATES = {
    "HAPP_ROUTING_MODE": "split",
    "HAPP_ROUTING_ENABLED": "true",
    "HAPP_ROUTING_NAME": '"AVA Split RU"',
    "HAPP_ROUTING_LAST_UPDATED": "1784060400",
    "HAPP_GEO_USE_BUILTIN": "false",
    "HAPP_GEO_USE_CHUNK_FILES": "false",
    "HAPP_SUBSCRIPTION_ANNOUNCE": '"Обновите подписку — исправлен роутинг: RU напрямую, заблокированное через VPN"',
}

lines = ENV.read_text(encoding="utf-8").splitlines()
keys = set(UPDATES)
out = []
seen = set()
for line in lines:
    if "=" in line and not line.lstrip().startswith("#"):
        key = line.split("=", 1)[0].strip()
        if key in UPDATES:
            out.append(f"{key}={UPDATES[key]}")
            seen.add(key)
            continue
    out.append(line)
for key, val in UPDATES.items():
    if key not in seen:
        out.append(f"{key}={val}")
ENV.write_text("\n".join(out) + "\n", encoding="utf-8")
print("env updated:", ", ".join(UPDATES))
