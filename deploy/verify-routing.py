#!/usr/bin/env python3
import base64, json, re, subprocess
hdr = subprocess.check_output(
    ['curl', '-sk', '-A', 'Happ/1.0', '-D', '-', 'https://avavpn.ru/sub/UbF3E1OAJt4cLqP3', '-o', '/dev/null'],
).decode()
m = re.search(r'routing: (happ://routing/onadd/[^\r\n]+)', hdr, re.I)
if not m:
    print('NO ROUTING')
    raise SystemExit(1)
d = json.loads(base64.b64decode(m.group(1).split('/')[-1]))
for k in ['Name','Geoipurl','Geositeurl','RouteOrder','UseChunkFiles','DomesticDNSIP','RemoteDNSIP','DomainStrategy']:
    print(f'{k}: {d.get(k)}')
print(f"DirectSites: {len(d.get('DirectSites',[]))}")
print(f"ProxySites: {len(d.get('ProxySites',[]))}")
print(f"BlockSites: {len(d.get('BlockSites',[]))}")
print('sample direct:', d['DirectSites'][:3])
print('sample proxy:', d['ProxySites'][:3])
