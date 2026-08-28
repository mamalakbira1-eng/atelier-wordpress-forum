import csv
import json
import subprocess
from datetime import datetime, timezone
from pathlib import Path

lab = Path('/home/ubuntu/atelier-cdc-20')
repo = Path(__file__).resolve().parents[2]
source = lab / 'matrice-cdc-complementaire-rc4-finale.csv'
out_dir = repo / 'docs' / 'validation'
rows = list(csv.DictReader(source.open(encoding='utf-8', newline='')))
updates = {
    'IMP-CL-05': ('PASS', '0', 'IMP-CL-05-import-interruption.json', 'Interruption après 2 objets, rollback vérifié, zéro objet marqué restant.'),
    'DEP-CL-03': ('PASS', '0', 'DEP-CL-03-uninstall.json', 'Politique écrite et test non destructif : tables et contenus conservés.'),
    'OPS-CL-06': ('PASS', '0', 'OPS-CL-06-cleanup.json', 'Compteurs de résidus et répertoires temporaires à zéro.'),
    'SEC-CL-05': ('PASS', '0', 'SEC-CL-05-private-cache.json', '10 observations authentifiées sur deux utilisateurs, directives private/no-store et zéro fuite cross-user.'),
    'IMP-CL-02': ('PASS', '0', 'IMP-CL-02-size-limit.json', 'Fichiers valides à 5 Mo moins un et plus un octet : sous-seuil accepté, dépassement refusé, delta base nul.'),
    'A11Y-CL-02': ('PASS', '0', 'A11Y-CL-02-keyboard.json', '6 pages x 20 transitions Tab, zéro focus invisible, zéro tabindex positif, recherche clavier exécutée.'),
    'IMP-CL-03': ('PASS', '0', 'IMP-CL-03-existing-duplicates.json', 'Second job sur objets existants : 4 correspondances et 0 création.'),
    'OPS-CL-04': ('PASS', '0', 'OPS-CL-04-restore-current.json', 'Restauration sur base et port isolés : accueil/topic HTTP 200, versions conservées, instance détruite.'),
    'OPS-CL-05': ('PASS', '0', 'OPS-CL-05-rpo-rto-current.json', 'RPO/RTO local mesuré sur le même cycle : backup 176 ms, RTO 7086 ms.'),
    'MOD-CL-02': ('PASS', '0', 'MOD-CL-02-capabilities.json', '15 assertions de capacités conformes; moderator peut modérer sans manage_options.'),
}
for row in rows:
    if row['id'] in updates:
        status, exit_code, evidence, summary = updates[row['id']]
        row.update(status=status, exit_code=exit_code, evidence=evidence, observed_summary=summary)
counts = {status: sum(row['status'] == status for row in rows) for status in sorted({row['status'] for row in rows})}
sha = subprocess.check_output(['git', '-C', str(repo), 'rev-parse', 'HEAD'], text=True).strip()
out_csv = out_dir / 'rc4-matrix-final.csv'
with out_csv.open('w', encoding='utf-8', newline='') as handle:
    writer = csv.DictWriter(handle, fieldnames=['id', 'status', 'exit_code', 'target', 'evidence', 'observed_summary'], lineterminator='\n')
    writer.writeheader()
    writer.writerows(rows)
out_json = out_dir / 'rc4-matrix-final.json'
out_json.write_text(json.dumps({'timestamp_utc': datetime.now(timezone.utc).isoformat(), 'commit': sha, 'objectives': len(rows), 'counts': counts, 'decision': 'NO-GO', 'reason': 'preuves locales restantes non rejouées; human/external scope conservé', 'email_scope': 'excluded', 'external_scope': 'blocked_external', 'human_scope': 'blocked_human'}, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
print(json.dumps({'commit': sha, 'objectives': len(rows), 'counts': counts, 'decision': 'NO-GO'}, ensure_ascii=False))
