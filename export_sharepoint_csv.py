#!/usr/bin/env python3
"""Export SharePoint site information from ifak.db to CSV.

This script reads departments, related facilities and owners from the
SQLite database `ifak.db` and outputs a CSV file compatible with the
expected SharePoint import format.

Example output:
    SiteUrl,Title,Owners,Folders
    https://tenant.sharepoint.com/sites/Department1,Department1,admin@tenant|department1-owner@tenant,"Facility01|Facility02|Facility03"
"""

from __future__ import annotations

import argparse
import csv
import sqlite3
from pathlib import Path
from typing import Iterable, List


def fetch_departments(cursor: sqlite3.Cursor) -> Iterable[sqlite3.Row]:
    """Return all departments.

    The returned rows expose the columns ``DepartmentID``, ``Short`` and
    ``Department`` (voller Name) wie in ``ifak.db.sql`` definiert.
    """

    cursor.execute(
        "SELECT DepartmentID, Short, Department FROM Departments ORDER BY Short"
    )
    return cursor.fetchall()


def fetch_facilities(cursor: sqlite3.Cursor, department_id: int) -> List[str]:
    """Return facility names associated with a department."""

    # Alle Einrichtungen (Facilities) zur Abteilung abfragen
    cursor.execute(
        "SELECT Facility FROM Facilities WHERE DepartmentID = ? ORDER BY Facility",
        (department_id,),
    )
    rows = cursor.fetchall()
    return [row["Facility"] for row in rows]


def fetch_owners(
    cursor: sqlite3.Cursor, department_id: int, role_name: str
) -> List[str]:
    """Return e-mail addresses of owners for a department.

    Owners are determined by employees linked to facilities of the
    department via ``FacilityLinks`` and having a role matching ``role_name``.
    """

    cursor.execute(
        """
        SELECT DISTINCT Employees.Mail AS mail
        FROM Employees
        JOIN FacilityLinks ON FacilityLinks.EmployeeID = Employees.EmployeeID
        JOIN Roles ON Roles.RoleID = FacilityLinks.RoleID
        JOIN Facilities ON Facilities.FacilityID = FacilityLinks.FacilityID
        WHERE Facilities.DepartmentID = ? AND Roles.RoleName = ? AND Employees.Mail != ''
        ORDER BY Employees.Mail
        """,
        (department_id, role_name),
    )
    rows = cursor.fetchall()
    # Eindeutige Besitzer anhand der Rolle ermitteln
    return [row["mail"] for row in rows]


def build_site_url(base_url: str, short_name: str) -> str:
    """Construct the SharePoint site URL for a department."""

    # Basis-URL bereinigen und Kurzname anhängen
    return f"{base_url.rstrip('/')}/{short_name}"


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--db",
        default="ifak.db",
        help="Path to the SQLite database (default: ifak.db)",
    )
    parser.add_argument(
        "--output",
        default="departments.csv",
        help="Path to write the CSV file (default: departments.csv)",
    )
    parser.add_argument(
        "--base-url",
        default="https://ifakbochum.sharepoint.com/sites/",
        help="Base URL for SharePoint sites",
    )
    parser.add_argument(
        "--owner-role",
        default="SharePoint Owner",
        help="Name of the role identifying site owners (default: 'SharePoint Owner')",
    )
    parser.add_argument(
        "--extra-owner",
        action="append",
        default=[],
        help="Additional owner e-mail address to add to every site (can be repeated)",
    )
    args = parser.parse_args()

    db_path = Path(args.db)
    if not db_path.exists():
        raise SystemExit(f"Database file {db_path} not found")

    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()

    departments = fetch_departments(cursor)

    with open(args.output, "w", newline="", encoding="utf-8") as csvfile:
        writer = csv.writer(csvfile)
        writer.writerow(["SiteUrl", "Title", "Owners", "Folders"])

        for dept in departments:
            # Auslesen der notwendigen Felder aus dem Datensatz
            dept_id = dept["DepartmentID"]
            short = dept["Short"]  # Kurzbezeichnung der Abteilung
            title = dept["Department"]  # Voller Name für den SharePoint-Titel

            # URL für die SharePoint-Seite zusammensetzen (Short wird verwendet)
            site_url = build_site_url(args.base_url, short)

            # Besitzer (Owners) der Seite ermitteln
            owners = fetch_owners(cursor, dept_id, args.owner_role)
            owners.extend(args.extra_owner)
            owners_field = "|".join(owners)

            # Zugeordnete Einrichtungen (Folders) ermitteln
            facilities = fetch_facilities(cursor, dept_id)
            folders_field = "|".join(facilities)

            # Datensatz in die CSV schreiben
            writer.writerow([site_url, title, owners_field, folders_field])

    conn.close()


if __name__ == "__main__":
    main()
