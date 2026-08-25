#!/usr/bin/env python3
"""Génère un pack CSV de recette PFC : 100 membres, 4 forums, 20 sujets et 80 réponses.

Les données sont strictement fictives et réservées au WordPress de staging.
"""

from __future__ import annotations

import csv
import json
import shutil
import unicodedata
from datetime import datetime, timedelta, timezone
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile


BASE_DIR = Path("/home/ubuntu/atelier-wordpress/test-import-massive")
ZIP_PATH = BASE_DIR / "atelier-recette-massive-100-membres.zip"

FIRST_NAMES = [
    "Aline", "Benoît", "Clara", "Damien", "Élodie", "Farid", "Gaëlle", "Hugo", "Inès", "Julien",
    "Karim", "Lina", "Mathieu", "Nora", "Olivier", "Priya", "Quentin", "Rania", "Sébastien", "Tara",
    "Ulysse", "Valérie", "Wassim", "Xavier", "Yara", "Zoé", "Adrien", "Béatrice", "Cédric", "Diane",
    "Elias", "Fanny", "Gaspard", "Hana", "Ismaël", "Jeanne", "Kamel", "Louise", "Mehdi", "Nadine",
    "Oscar", "Pauline", "Romain", "Salma", "Théo", "Uma", "Victor", "Widad", "Yanis", "Aïcha",
    "Baptiste", "Chloé", "Dario", "Estelle", "Fouad", "Giulia", "Hervé", "Iris", "Jamal", "Kenza",
    "Léo", "Maya", "Nicolas", "Ophélie", "Pascal", "Rita", "Sofiane", "Tiphaine", "Ulrich", "Véronique",
    "Walid", "Ximena", "Youssef", "Zineb", "Anaïs", "Bruno", "Célia", "Dylan", "Eve", "Franck",
    "Gina", "Hassan", "Ilona", "Jérôme", "Katia", "Loris", "Mouna", "Noé", "Oriana", "Perrine",
    "Rachid", "Sonia", "Tanguy", "Ugo", "Violaine", "Wafa", "Xéna", "Yves", "Zara", "Émile",
]

LAST_NAMES = [
    "Alvarez", "Boucher", "Charpentier", "Dumont", "Evrard", "Fischer", "Garnier", "Haddad", "Ibrahim", "Joubert",
    "Klein", "Lefort", "Moreau", "Nguyen", "Olivier", "Perrin", "Quessada", "Roux", "Sanchez", "Thomas",
    "Ulmann", "Vidal", "Wagner", "Xavier", "Yilmaz", "Ziani", "Arnaud", "Bertin", "Carlier", "Delmas",
    "Elbaz", "Fontaine", "Giraud", "Hamel", "Ismail", "Jacquet", "Khelifi", "Lemoine", "Ménard", "Noël",
    "Ortega", "Picard", "Quintin", "Renard", "Sabatier", "Tessier", "Ubertini", "Vasseur", "Weber", "Youssef",
    "Aubert", "Barbier", "Chauvin", "Delaunay", "Eymard", "Ferrand", "Guillon", "Hervieu", "Icard", "Janvier",
    "Kermorvan", "Lacombe", "Marchand", "Nivelle", "Ollier", "Perrot", "Quirin", "Riviere", "Schaeffer", "Turpin",
    "Urbain", "Vernier", "Willems", "Xerri", "Ybert", "Zuccarelli", "Aubry", "Blin", "Couturier", "Dufour",
    "Estève", "Fabre", "Gallet", "Hamon", "Imbert", "Jarrige", "Kouyaté", "Lorin", "Masson", "Noguès",
    "Orsini", "Poulain", "Quéméner", "Rousseau", "Sénéchal", "Tournier", "Urbano", "Veyrier", "Walter", "Yvon",
]

FORUMS = [
    ("f-2001", "Méthodes de travail", "Outils, routines et retours pour organiser un travail exigeant."),
    ("f-2002", "Décisions et documentation", "Conserver les hypothèses, arbitrages et sources de décisions."),
    ("f-2003", "Recherche et veille", "Qualifier les sources, structurer la veille et partager les signaux utiles."),
    ("f-2004", "Pratiques d’équipe", "Rituels, facilitation et transmission dans les équipes de travail."),
]

TOPIC_SEEDS = [
    ("Comment faire un journal de décision sans alourdir les projets ?", "Comment faire un journal de décision sans alourdir les projets"),
    ("Quelle cadence de revue pour une documentation vivante ?", "quelle-cadence-de-revue-pour-une-documentation-vivante"),
    ("Comment structurer une veille partagée sans bruit ?", "structurer-une-veille-partagee-sans-bruit"),
    ("Quels signaux indiquent qu’un processus doit être simplifié ?", "signaux-processus-a-simplifier"),
    ("Comment formuler une décision réversible et vérifiable ?", "formuler-une-decision-reversible"),
    ("Quel format de compte rendu rend vraiment les réunions utiles ?", "format-compte-rendu-reunion-utile"),
    ("Comment éviter la perte de contexte lors d’un changement d’équipe ?", "eviter-perte-contexte-changement-equipe"),
    ("Quelles sources citer dans une note de synthèse courte ?", "sources-note-synthese-courte"),
    ("Comment distinguer fait, hypothèse et interprétation dans un dossier ?", "distinguer-fait-hypothese-interpretation"),
    ("Quel rituel aide à capitaliser après une expérimentation ?", "rituel-capitaliser-apres-experimentation"),
    ("Comment concevoir un espace de questions utiles pour une équipe ?", "concevoir-espace-questions-utiles"),
    ("Quelle méthode pour relire une procédure avant sa publication ?", "methode-relire-procedure-publication"),
    ("Comment indexer des ressources internes pour les retrouver vite ?", "indexer-ressources-internes"),
    ("Comment documenter les options écartées sans les survaloriser ?", "documenter-options-ecartees"),
    ("Quels indicateurs suivre pour une base de connaissances ?", "indicateurs-base-connaissances"),
    ("Comment organiser une passation de dossier en une semaine ?", "organiser-passation-dossier"),
    ("Quel niveau de détail pour une fiche de décision stratégique ?", "niveau-detail-fiche-decision"),
    ("Comment rendre les désaccords consultables sans créer de tensions ?", "rendre-desaccords-consultables"),
    ("Comment garder un historique lisible quand un document évolue ?", "historique-lisible-document-evolue"),
    ("Quelles règles simples pour répondre utilement sur un forum métier ?", "regles-repondre-forum-metier"),
]

BIO_PREFIXES = [
    "Contribue aux méthodes de travail et à la transmission des savoirs.",
    "Partage des pratiques de documentation claires et vérifiables.",
    "S’intéresse à la qualité des décisions collectives.",
    "Explore des outils légers pour mieux collaborer.",
]

REPLY_OPENERS = [
    "Je commencerais par rendre la question et son contexte immédiatement visibles.",
    "Une règle utile consiste à conserver le minimum de trace qui explique l’arbitrage.",
    "Dans notre équipe, ce qui fonctionne le mieux est une source courte et régulièrement relue.",
    "Je proposerais de séparer les faits vérifiés, les hypothèses et la prochaine étape.",
]


def stamp(moment: datetime) -> str:
    return moment.astimezone(timezone.utc).strftime("%Y-%m-%dT%H:%M:%S+00:00")


def ascii_slug(value: str) -> str:
    """Convertit un nom affiché en identifiant ASCII valide pour un email de test."""
    normalized = unicodedata.normalize("NFKD", value)
    return "".join(character for character in normalized if not unicodedata.combining(character)).lower().replace(" ", "-")


def write_csv(path: Path, fieldnames: list[str], rows: list[dict[str, object]]) -> None:
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)


def validate_pack(users: list[dict[str, object]], forums: list[dict[str, object]], topics: list[dict[str, object]], replies: list[dict[str, object]]) -> dict[str, int]:
    user_ids = {str(row["legacy_user_id"]) for row in users}
    forum_ids = {str(row["legacy_forum_id"]) for row in forums}
    topic_ids = {str(row["legacy_topic_id"]) for row in topics}
    assert len(user_ids) == 100, "Les IDs membres doivent être uniques."
    assert len({str(row["email"]) for row in users}) == 100, "Les emails doivent être uniques."
    assert len(forum_ids) == 4 and len(topic_ids) == 20 and len(replies) == 80, "Les volumes de recette sont incohérents."
    assert all(not row["password_hash"] for row in users), "Aucun mot de passe ne doit être fourni."
    assert all(str(row["legacy_forum_id"]) in forum_ids for row in topics), "Un sujet référence un forum absent."
    assert all(str(row["legacy_author_id"]) in user_ids for row in topics), "Un sujet référence un auteur absent."
    assert all(str(row["legacy_topic_id"]) in topic_ids for row in replies), "Une réponse référence un sujet absent."
    assert all(str(row["legacy_author_id"]) in user_ids for row in replies), "Une réponse référence un auteur absent."
    assert all(int(row["upvotes_count"]) >= 0 for row in topics + replies), "Les votes doivent être positifs ou nuls."
    return {
        "users": len(users),
        "forums": len(forums),
        "topics": len(topics),
        "replies": len(replies),
        "referenced_users": len({str(row["legacy_author_id"]) for row in topics + replies}),
    }


def main() -> None:
    if BASE_DIR.exists():
        shutil.rmtree(BASE_DIR)
    BASE_DIR.mkdir(parents=True)

    users: list[dict[str, object]] = []
    for index, (first, last) in enumerate(zip(FIRST_NAMES, LAST_NAMES), start=1):
        username = f"{ascii_slug(first)}-{ascii_slug(last)}"
        users.append({
            "legacy_user_id": f"u-{2000 + index}",
            "username": username,
            "email": f"{username}+recette{index}@example.test",
            "first_name": first,
            "last_name": last,
            "display_name": f"{first} {last}",
            "registered_at": stamp(datetime(2021, 1, 1, tzinfo=timezone.utc) + timedelta(days=index * 9)),
            "bio": BIO_PREFIXES[(index - 1) % len(BIO_PREFIXES)],
            "rank": ["Éclaireur", "Contributrice", "Cartographe", "Archiviste"][index % 4],
            "role": "subscriber",
            "status": "active",
            "password_hash": "",
        })

    forums = [
        {
            "legacy_forum_id": forum_id,
            "title": title,
            "description": description,
            "parent_legacy_forum_id": "",
            "status": "public",
            "sort_order": number,
        }
        for number, (forum_id, title, description) in enumerate(FORUMS, start=1)
    ]

    base_date = datetime(2024, 1, 8, 9, 0, tzinfo=timezone.utc)
    topics: list[dict[str, object]] = []
    replies: list[dict[str, object]] = []
    for index, (title, slug) in enumerate(TOPIC_SEEDS, start=1):
        author_id = f"u-{2000 + index}"
        topic_date = base_date + timedelta(days=index * 7, hours=index % 5)
        topics.append({
            "legacy_topic_id": f"t-{2000 + index}",
            "legacy_forum_id": FORUMS[(index - 1) % len(FORUMS)][0],
            "legacy_author_id": author_id,
            "title": title,
            "content": (
                "Je cherche un retour d’expérience concret sur cette question. "
                "L’objectif est de conserver une information utile, de citer les éléments vérifiables "
                "et de pouvoir relire la décision plusieurs mois plus tard."
            ),
            "created_at": stamp(topic_date),
            "updated_at": stamp(topic_date + timedelta(days=2, hours=3)),
            "status": "publish",
            "slug": slug,
            "upvotes_count": 12 + (index * 7) % 83,
            "replies_count": 4,
            "is_sticky": 1 if index in (1, 3) else 0,
            "is_resolved": 1 if index % 3 == 0 else 0,
        })
        for reply_order in range(1, 5):
            responder_number = 20 + ((index - 1) * 4 + reply_order)
            responder_id = f"u-{2000 + responder_number}"
            reply_date = topic_date + timedelta(days=reply_order, hours=reply_order * 2)
            replies.append({
                "legacy_reply_id": f"r-{2000 + (index - 1) * 4 + reply_order}",
                "legacy_topic_id": f"t-{2000 + index}",
                "legacy_author_id": responder_id,
                "content": (
                    f"{REPLY_OPENERS[(index + reply_order - 2) % len(REPLY_OPENERS)]} "
                    "Je documenterais aussi la date de revue, la source principale et la limite de validité."
                ),
                "created_at": stamp(reply_date),
                "updated_at": stamp(reply_date + timedelta(hours=1)),
                "status": "publish",
                "upvotes_count": 3 + (index * 5 + reply_order * 3) % 37,
                "legacy_parent_reply_id": "",
                "sort_order": reply_order,
            })

    write_csv(BASE_DIR / "users.csv", list(users[0].keys()), users)
    write_csv(BASE_DIR / "forums.csv", list(forums[0].keys()), forums)
    write_csv(BASE_DIR / "topics.csv", list(topics[0].keys()), topics)
    write_csv(BASE_DIR / "replies.csv", list(replies[0].keys()), replies)

    manifest = validate_pack(users, forums, topics, replies)
    (BASE_DIR / "validation-manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )

    with ZipFile(ZIP_PATH, "w", ZIP_DEFLATED) as archive:
        for filename in ("users.csv", "forums.csv", "topics.csv", "replies.csv"):
            archive.write(BASE_DIR / filename, arcname=filename)

    print(f"Pack créé : {ZIP_PATH}")
    print(f"Membres={len(users)} Forums={len(forums)} Sujets={len(topics)} Réponses={len(replies)}")
    print(f"Validation={json.dumps(manifest, ensure_ascii=False)}")


if __name__ == "__main__":
    main()
