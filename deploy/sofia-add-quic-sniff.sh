#!/bin/bash
set -euo pipefail
python3 <<'PY'
import json
path = "/usr/local/etc/xray/config.json"
with open(path) as f:
    cfg = json.load(f)
cfg["inbounds"][0]["sniffing"]["destOverride"] = ["http", "tls", "quic"]
with open(path, "w") as f:
    json.dump(cfg, f, indent=2)
    f.write("\n")
PY
systemctl restart xray
systemctl is-active xray
