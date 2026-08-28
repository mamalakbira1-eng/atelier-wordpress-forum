import csv, json
from pathlib import Path
p = Path(__file__).resolve().parents[2] / 'docs' / 'validation' / 'rc4-matrix-final.csv'
with p.open(encoding='utf-8', newline='') as f:
    rows = list(csv.DictReader(f))
counts = {}
for row in rows:
    counts[row['status']] = counts.get(row['status'], 0) + 1
print(json.dumps({'rows': len(rows), 'counts': counts, 'blocked': [r for r in rows if r['status'] != 'PASS']}, ensure_ascii=False, indent=2))
