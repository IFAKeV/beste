import sqlite3
import json

def dict_factory(cursor, row):
    """
    Convert fetched rows into a dictionary with column names as keys.
    """
    d = {}
    for idx, col in enumerate(cursor.description):
        d[col[0]] = row[idx]
    return d

def export_data(db_path, output_path):
    # Verbindung zur SQLite-Datenbank herstellen
    conn = sqlite3.connect(db_path)
    conn.row_factory = dict_factory  # Setzen der row_factory
    cursor = conn.cursor()
    
    # Alle Mitarbeiter abrufen
    cursor.execute("SELECT * FROM Employees ORDER BY SortedLastName ASC")
    employees = cursor.fetchall()

    persons = []

    for emp in employees:
        person = {
            "id": emp["EmployeeID"],
            "name": f"{emp['FirstName']} {emp['LastName']}",
            "email": emp["Mail"],
            "phone": emp["Phone"],
            "mobile": emp["Mobile"],
#             "address": "",  # Hier Adresse ergänzen, falls vorhanden
        }

        # Einrichtungen für den Mitarbeiter abrufen
#         cursor.execute("""
#             SELECT Facilities.*
#             FROM Facilities
#             INNER JOIN FacilityLinks ON Facilities.FacilityID = FacilityLinks.FacilityID
#             WHERE FacilityLinks.EmployeeID = ?
#         """, (emp["EmployeeID"],))
        # Einrichtungen der Person abrufen, inklusive role-Feld
        cursor.execute("""
            SELECT FacilityLinks.FacilityID, FacilityLinks.RoleID
            FROM FacilityLinks
            WHERE FacilityLinks.EmployeeID = ?
            """, (emp["EmployeeID"],))
#        facilities = cursor.fetchall()
        facilities_data = cursor.fetchall()
	    # Facilities-Array erstellen
        facility_entries = []
        for facility_row in facilities_data:
            facility_id = facility_row['FacilityID']
            role_id = facility_row['RoleID'] if facility_row['RoleID'] is not None else 0  # Standardwert 0

            facility_entries.append({
                'facilityId': facility_id,
                'role': role_id
            })


        # Facilities der Person zuweisen
        person['facilities'] = facility_entries

        # Füge die Person zu den Daten hinzu
        #data['persons'].append(person)


        # Facility IDs sammeln
        #facility_ids = [facility["FacilityID"] for facility in facilities]
		
        # Immer das Feld "facilities" verwenden, auch wenn es leer ist
        #person["facilities"] = facility_ids

        # Facility IDs sammeln
#         facility_ids = [facility["FacilityID"] for facility in facilities]
# 
#         if len(facility_ids) == 1:
#             person["facility"] = facility_ids[0]
#         elif len(facility_ids) > 1:
#             person["facilities"] = facility_ids
#         else:
            # Falls keine Einrichtungen verknüpft sind, können Sie dieses Feld weglassen oder auf None setzen
			# person["facility"] = None  # Optional

		
        # Sprachen für den Mitarbeiter abrufen
        cursor.execute("""
            SELECT Languages.LanguageName AS name, LanguageLinks.SkillLevel AS level, LanguageLinks.zertifiziert AS certified
            FROM Languages
            INNER JOIN LanguageLinks ON Languages.LanguageID = LanguageLinks.LanguageID
            WHERE LanguageLinks.EmployeeID = ?
        """, (emp["EmployeeID"],))
        languages = cursor.fetchall()

        # Sprachen formatieren
        person_languages = []
        for lang in languages:
            person_languages.append({
                "name": lang["name"],
                "level": lang["level"],
                "certified": bool(lang["certified"])
            })

        person["languages"] = person_languages

        persons.append(person)

    # Alle Einrichtungen abrufen
    cursor.execute("SELECT * FROM Facilities ORDER BY SortedLong ASC")
    facilities = cursor.fetchall()

    facility_list = []
    for fac in facilities:
        facility = {
            "id": fac["FacilityID"],
            "name": fac["Facility"],
            "email": fac["Mail"],
            "phone": fac["Phone"],
            "fax": fac["Fax"],
            "mobile": fac["Mobile"],
            "url": fac["URL"],                        
            "location": fac["LocationID"],  # LocationID referenziert den Standort
            "department": fac["DepartmentID"]  # DepartmentID referenziert den Fachbereich
        }
        facility_list.append(facility)

    # Alle Standorte abrufen
    cursor.execute("SELECT * FROM Locations ORDER BY SortedLong COLLATE NOCASE ASC")
    locations = cursor.fetchall()

    location_list = []
    for loc in locations:
        location = {
            "id": loc["LocationID"],
            "name": loc["Location"],
            "address": f"{loc['Street']}, {loc['ZIP']} {loc['Town']}"
        }
        location_list.append(location)

    # Alle Fachbereiche abrufen
    cursor.execute("SELECT * FROM Departments ORDER BY SortedLong COLLATE NOCASE ASC")
    departments = cursor.fetchall()

    department_list = []
    for dep in departments:
        department = {
            "id": dep["DepartmentID"],
            "name": dep["Department"],
            "color": dep["color"]
        }
        department_list.append(department)

    # Alle Rollen abrufen
    cursor.execute("SELECT * FROM Roles")
    roles = cursor.fetchall()

    role_list = []
    for rol in roles:
        role = {
            "id": rol["RoleID"],
            "name": rol["RoleName"],
            "sign": rol["RoleSign"]
        }
        role_list.append(role)

    # Gesamte Datenstruktur erstellen
    data = {
        "persons": persons,
        "facilities": facility_list,
        "locations": location_list,
        "departments": department_list,
        "roles": role_list
    }

    # In JSON-Datei schreiben
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    # Verbindung schließen
    conn.close()

# Verwendung des Skripts
export_data('/var/www/html/beste/ifak.db', '/var/www/html/beste/ifak.json')