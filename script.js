let data = { persons: [], facilities: [], locations: [], departments: [] };

// Liste von Synonymen und Übersetzungen für "Beste"
const word1List = [
	'Best', 'Beste', 'Mejor', 'Meilleur', 'Migliore', 'Paras', 'Лучший', '最好', 'Nejlepší', 'Terbaik', 'Maikai loa', 'Më i miri', 'Pinakamahusay', 'Bäst', 'Melhor', 'Legjobb', 'Najlepszy', '最高', '최고', 'En iyi', 'Καλύτερος', 'أفضل', 'הטוב ביותר', 'सबसे अच्छा', 'সর্বোত্তম', 'Behtarin', 'Найкращий', 'Najbolji', 'Najbolje', 'Най-добър', 'Najboljši', 'Parim', 'Labākais', 'Geriausias', 'Bestur', 'Tốt nhất', 'ดีที่สุด', 'Atyuttama', 'Siṟanta', 'Is fearr', 'Gorau', 'Хамгийн сайн', 'nIvqu’', 'Plej bona', 'Pai rawa atu', 'Sili', 'Kuhle kakhulu', 'Eyona ilungileyo', 'Dara julọ', 'Kasị mma', 'Mafi kyau', 'Chakanakisisa', 'Pi bon', 'Herî baş', 'Lavaguyn', 'Sauketeso', 'Kefetegna', 'ល្អបំផុត', 'အကောင်းဆုံး'
];

// Liste von Synonymen und Übersetzungen für "Family"
const word2List = [
    'Family', 'Familie', 'Familia', 'Famille', 'Famiglia', 'Perhe', 'Семья', '家庭', 'Rodina', 'Keluarga', 'Ohana', 'Ohano', 'Perheen', 'Familja', 'Pamilya', 'Familjen', 'Família', 'Család', 'Rodzina', 'Kazoku', 'Gajok', 'Aile', 'Oikogéneia', 'Usra', 'Mishpacha', 'Parivaar', 'Poribar', 'Khandan', 'Khanevadeh', 'Porodica', 'Obitelj', 'Семейство', 'Družina', 'Perekond', 'Ģimene', 'Šeima', 'Fjölskylda', 'Gia đình', 'ครอบครัว', 'Kutumba', 'Kudumbam', 'Clann', 'Teaghlach', 'Teulu', 'Ger bul', 'Семья','tuq', 'nossë', 'Familio', 'Whānau', 'Aiga', 'Umndeni', 'Usapho', 'Ẹbí', 'Ezinụlọ', 'Iyali', 'Mhuri', 'Fanmi', 'Malbat', 'Ĕntanik', 'Ojakhi', 'Beteseb', 'Kruosa', 'Mi thaa zu'
];

async function loadData() {
    try {
        console.log("Attempting to load data...");
        const response = await fetch('ifak.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        data = await response.json();
        if (!data.departments) {
            data.departments = [];
        }
        console.log("Data loaded successfully:", data);
        renderData();
    } catch (error) {
        console.error('Error loading data:', error);
        document.getElementById('dataMosaic').innerHTML = `<p>Error loading data: ${error.message}</p>`;
    }
}

function updateHeaderWords() {
    const word1Element = document.getElementById('word1');
    const word2Element = document.getElementById('word2');

    // Fade-out nur für WORT1 und WORT2
    word1Element.classList.add('fade-out');
    word2Element.classList.add('fade-out');

    setTimeout(() => {
        // Neue Wörter zufällig auswählen
        const word1 = word1List[Math.floor(Math.random() * word1List.length)];
        const word2 = word2List[Math.floor(Math.random() * word2List.length)];

        // Wörter aktualisieren
        word1Element.textContent = word1;
        word2Element.textContent = word2;

        // Fade-in nur für WORT1 und WORT2
        word1Element.classList.remove('fade-out');
        word2Element.classList.remove('fade-out');
    }, 2000); // Wartezeit entspricht der CSS-Transition-Dauer
}

function renderData(filterType = 'all', languageFilter = null, languageLevelFilter = null) {
    console.log("Rendering data...");

    let filteredData = [];

    if (filterType === 'all' || filterType === 'person') {
        filteredData = filteredData.concat(data.persons.filter(person => {
            const matchesLanguage = !languageFilter || person.languages.some(lang => lang.name === languageFilter);
            const matchesLanguageLevel = !languageLevelFilter || person.languages.some(lang => `${lang.name}-${lang.level}` === languageLevelFilter);
            return matchesLanguage && matchesLanguageLevel;
        }));
    }
    if (filterType === 'all' || filterType === 'facility') {
        filteredData = filteredData.concat(data.facilities);
    }
    if (filterType === 'all' || filterType === 'location') {
        filteredData = filteredData.concat(data.locations);
    }

    console.log("Filtered data:", filteredData);

    renderCards(filteredData, languageFilter, languageLevelFilter);
}

function filterAndRenderData(searchTerm, filterType, languageFilter = null, languageLevelFilter = null) {
    let filteredData = [];

    if (filterType === 'all' || filterType === 'person') {
        filteredData = filteredData.concat(data.persons.filter(person => {
            const name = person.name || '';
            const email = person.email || '';
            const phone = person.phone || '';
            const mobile = person.mobile || '';

            const matchesSearch = name.toLowerCase().includes(searchTerm) ||
                email.toLowerCase().includes(searchTerm) ||
                phone.includes(searchTerm) ||
                mobile.includes(searchTerm);

            const matchesLanguage = !languageFilter || (
                person.languages &&
                person.languages.some(lang => lang.name === languageFilter)
            );
            const matchesLanguageLevel = !languageLevelFilter || (
                person.languages &&
                person.languages.some(lang => `${lang.name}-${lang.level}` === languageLevelFilter)
            );

            return matchesSearch && matchesLanguage && matchesLanguageLevel;
        }));
    }

    if (filterType === 'all' || filterType === 'facility') {
        filteredData = filteredData.concat(data.facilities.filter(facility => {
            const name = facility.name || '';
            return name.toLowerCase().includes(searchTerm);
        }));
    }

    if (filterType === 'all' || filterType === 'location') {
        filteredData = filteredData.concat(data.locations.filter(location => {
            const name = location.name || '';
            const address = location.address || '';
            return name.toLowerCase().includes(searchTerm) ||
                address.toLowerCase().includes(searchTerm);
        }));
    }

    renderCards(filteredData, languageFilter, languageLevelFilter);
}

function renderCards(filteredData, languageFilter = null, languageLevelFilter = null) {
    const mosaic = document.getElementById('dataMosaic');
    mosaic.innerHTML = '';

    filteredData.forEach(item => {
        let card;
        if (item.hasOwnProperty('languages')) {
            let details = '';
            // Einrichtungen hinzufügen
            if (item.facilities && item.facilities.length > 0) {
//                 let facilitiesText = item.facilities.map(facilityId => getFacilityName(facilityId)).join(', ');
				let facilitiesText = item.facilities
					.map(facilityRef => getFacilityName(facilityRef.facilityId))
					.join(', ');
                details += `${facilitiesText}<br>`;
            }

            if (item.email) {
                details += `&#9993; ${item.email}<br>`;
            }
            let phones = [];
            const fiveDigitAreaCodes = ['02323', '02325', '02327', '02302'];

            function formatPhoneNumber(number) {
                number = number.replace(/\s+/g, '');
                const areaCodeFiveDigits = number.slice(0, 5);

                if (fiveDigitAreaCodes.includes(areaCodeFiveDigits)) {
                    return number.slice(0, 5) + '/' + number.slice(5);
                } else {
                    return number.slice(0, 4) + '/' + number.slice(4);
                }
            }

            if (item.phone) {
                let formattedPhone = formatPhoneNumber(item.phone);
                phones.push(`&#9743; ${formattedPhone}`);
            }
            if (item.mobile) {
                let formattedMobile = item.mobile.slice(0, 4) + '/' + item.mobile.slice(4);
                phones.push(`&#9647; ${formattedMobile}`);
            }
            if (phones.length > 0) {
                details += phones.join(' / ') + '<br>';
            }

            if (item.department) {
                const deptName = getDepartmentName(item.department);
                details += `<span class="department-label">${deptName}</span>`;
            }

            card = createCard('person', item.name, details, item, item.department);
        } else if (item.hasOwnProperty('location')) {
            let details = '';

            if (item.email) {
                details += `&#9993; ${item.email}<br>`;
            }
            let phones = [];
            const fiveDigitAreaCodes = ['02323', '02325', '02327', '02302'];

            function formatPhoneNumber(number) {
                number = number.replace(/\s+/g, '');
                const areaCodeFiveDigits = number.slice(0, 5);

                if (fiveDigitAreaCodes.includes(areaCodeFiveDigits)) {
                    return number.slice(0, 5) + '/' + number.slice(5);
                } else {
                    return number.slice(0, 4) + '/' + number.slice(4);
                }
            }

            if (item.phone) {
                let formattedPhone = formatPhoneNumber(item.phone);
                phones.push(`&#9743; ${formattedPhone}`);
            }
            if (item.mobile) {
                let formattedMobile = item.mobile.slice(0, 4) + '/' + item.mobile.slice(4);
                phones.push(`&#9647; ${formattedMobile}`);
            }            
            if (phones.length > 0) {
                details += phones.join(' / ') + '<br>';
            }
            details += `Standort: ${getLocationName(item.location)}`;
            card = createCard('facility', item.name, details, item);
//             card = createCard('facility', item.name, , item);
        } else {
            card = createCard('location', item.name, item.address, item);
        }
        mosaic.appendChild(card);
    });

    updateFilterDisplay(languageFilter, languageLevelFilter);
}

function createCard(type, title, details, fullData, departmentId = null) {
    const card = document.createElement('div');
    card.className = `card ${type}-card`; // Hier wird die Typ-spezifische Klasse hinzugefügt
    card.innerHTML = `
        <div class="card-title">${title}</div>
        <div class="card-details">${details}</div>
    `;
    if (type === 'person' && departmentId) {
        const color = getDepartmentColor(departmentId);
        card.style.borderRight = `8px solid ${color}`;
    }
    card.addEventListener('click', () => showDetails(type, fullData));
    return card;
}

function showDetails(type, itemData) {
    const modal = document.getElementById('detailModal');
//     const content = document.getElementById('modalContent');
    const content = modal.querySelector('.modal-content'); // Ändern Sie dies, um das Element mit der Klasse 'modal-content' zu erhalten
    let detailsHtml = '';
    let phones = [];
    const fiveDigitAreaCodes = ['02323', '02325', '02327', '02302'];

    function formatPhoneNumber(number) {
        number = number.replace(/\s+/g, '');
        const areaCodeFiveDigits = number.slice(0, 5);

        if (fiveDigitAreaCodes.includes(areaCodeFiveDigits)) {
            return number.slice(0, 5) + '/' + number.slice(5);
        } else {
            return number.slice(0, 4) + '/' + number.slice(4);
        }
    }
 	let formattedPhone='';
    if (itemData.phone) {
        formattedPhone = formatPhoneNumber(itemData.phone);
    }
    
    let formattedMobile='';
    if (itemData.mobile) {
        formattedMobile = itemData.mobile.slice(0, 4) + '/' + itemData.mobile.slice(4);
    }
 	let formattedFax='';
    if (itemData.fax) {
        formattedFax = formatPhoneNumber(itemData.fax);
    }

    switch(type) {
        case 'person':
        
			// Einrichtungen verarbeiten
            let facilitiesHtml = '';
            if (itemData.facilities && itemData.facilities.length > 0) {
                facilitiesHtml = '<p>Einrichtungen:</p><ul>';
                itemData.facilities.forEach(facilityRef => {
                    const facilityId = facilityRef.facilityId;
                    const role = facilityRef.role;

                    // Wähle das passende Symbol und die Rolle
                    let symbol = '';
                    let roleText = '';
                    switch(role) {
                        case 1:
                            symbol = '&#9733;'; // Stern für Leitung
                            roleText = 'Leitung';
                            break;
                        case 2:
                            symbol = '&#9734;'; // Leerer Stern für Co-Leitung
                            roleText = 'Stellvertretende Leitung';
                            break;
                        case 3:
                            symbol = '&#9733;'; // Stern für Vorsitzende
                            roleText = 'Vorsitzende';
                            break;
                        case 4:
                            symbol = '&#9733;'; // Stern für Vorsitzende
                            roleText = 'Geschäftsführerin';
                            break;
                        case 5:
                            symbol = '&#9675;'; // Stern für Vorsitzende
                            roleText = 'Auszubildender';
                            break;
                        default:
                            symbol = '&#8226;'; // Punkt für reguläres Mitglied
                            roleText = 'Team';
                            break;
                    }

                    facilitiesHtml += `<li style="list-style-type: none;">${symbol} <a href="#" data-type="facility" data-id="${facilityId}">${getFacilityName(facilityId)}</a> <span style="color:#888">/ ${roleText}</span></li>`;
                });
                facilitiesHtml += '</ul>';
            } else {
                facilitiesHtml = '<p>Einrichtungen: Keine verknüpften Einrichtungen</p>';
            }

                            detailsHtml = `<a href="index.php?main=vz&EmployeeID=${itemData.id}" class="vz-link">VZ</a><h2>${itemData.name}</h2>${facilitiesHtml}`;
						
			    // Nur hinzufügen, wenn Telefon vorhanden ist
			    if (itemData.phone) {
			        detailsHtml += `<p>Telefon: <a href="tel:${itemData.phone}">${formattedPhone}</a></p>`;
			    }
			
			    // Nur hinzufügen, wenn Mobil vorhanden ist
			    if (itemData.mobile) {
			        detailsHtml += `<p>Mobil: <a href="tel:${itemData.mobile}">${formattedMobile}</a></p>`;
			    }

			    // Nur hinzufügen, wenn Email vorhanden ist
			    if (itemData.email) {
			        detailsHtml += `<p>Email: <a href="mailto:${itemData.email}">${itemData.email}</a></p>`;
			    }
			
			    // Nur hinzufügen, wenn Sprachen vorhanden sind
                            if (itemData.languages && itemData.languages.length > 0) {
                                detailsHtml += `<p>Sprachen:</p>
                                <ul class="languages">
                                    ${itemData.languages.map(lang => `
                                        <li>
                                            <a href="#" data-filter="language" data-value="${lang.name}">${lang.name}</a>:
                                            <a href="#" data-filter="language-level" data-value="${lang.name}-${lang.level}" class="language-level ${lang.certified ? 'certified' : ''}">${lang.level}</a>
                                            ${lang.certified ? ' (Zertifiziert)' : ''}
                                        </li>
                                    `).join('')}
                                </ul>`;
                            }

                            if (itemData.department) {
                                detailsHtml += `<p>Fachbereich: ${getDepartmentName(itemData.department)}</p>`;
                            }
			
            break;
            case 'facility':
            
	            const personsInFacility = getPersonsInFacility(itemData.id);
			    let personsHtml = '<h3>Team:</h3><ul>';
			    personsInFacility.forEach(({ person, role }) => {
			        let symbol = '';
			        let roleText = '';
			        switch(role) {
			            case 1:
			                symbol = '&#9733;'; // Stern für Leitung
			                roleText = 'Leitung';
			                break;
			            case 2:
			                symbol = '&#9734;'; // Leerer Stern für Stellvertretung
			                roleText = 'Stellvertretende Leitung';
			                break;
                        case 3:
                            symbol = '&#9733;'; // Stern für Vorsitzende
                            roleText = 'Vorsitzende';
                            break;
                        case 4:
                            symbol = '&#9733;'; // Stern für Vorsitzende
                            roleText = 'Geschäftsführerin';
                            break;
                        case 5:
                            symbol = '&#9675;'; // Stern für Vorsitzende
                            roleText = 'Auszubildender';
                            break;
			            default:
			                symbol = '&#8226;'; // Punkt für reguläres Mitglied
			                roleText = 'Team';
			                break;
			        }
			
			        personsHtml += `<li style="list-style-type: none;">${symbol} <a href="#" data-type="person" data-id="${person.id}">${person.name}</a> <span style="color:#888">/ ${roleText}</span></li>`;
			    });
			    personsHtml += '</ul>';

			    detailsHtml = `
			        <h2>Einrichtung / Projekt</h2>
			        <h1>${itemData.name}</h1>
			        <p>Standort: <a href="#" data-type="location" data-id="${itemData.location}">${getLocationName(itemData.location)}</a></p>
			    `;
			
			    if (itemData.phone) {
			        detailsHtml += `<p>Telefon: <a href="tel:${itemData.phone}">${formattedPhone}</a></p>`;
			    }
			
			    if (itemData.mobile) {
			        detailsHtml += `<p>Mobil: <a href="tel:${itemData.mobile}">${formattedMobile}</a></p>`;
			    }
			
			    if (itemData.fax) {
			        detailsHtml += `<p>Fax: ${formattedFax}</p>`;
			    }
			
			    if (itemData.email) {
			        detailsHtml += `<p>Email: <a href="mailto:${itemData.email}">${itemData.email}</a></p>`;
			    }
			
			    if (itemData.url) {
			        detailsHtml += `<p>Webseite: <a target="_blank" href="${itemData.url}">${itemData.url}</a></p>`;
			    }
			
			    detailsHtml += personsHtml;
            break;
			case 'location':
			
			    detailsHtml = `
			        <h2>Standort / Betriebsstätte</h2>
			        <h1>${itemData.name}</h1>
			    `;
			
			    if (itemData.address) {
			        detailsHtml += `<p>Adresse: <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(itemData.address)}" target="_blank">${itemData.address}</a></p>`;
			    }
			
			    const facilitiesInLocation = getFacilitiesInLocation(itemData.id);
			
			    if (facilitiesInLocation && facilitiesInLocation.length > 0) {
			        detailsHtml += `<h3>Einrichtungen/Projekte:</h3>
			        <ul>${facilitiesInLocation.map(f => `<li><a href="#" data-type="facility" data-id="${f.id}">${f.name}</a></li>`).join('')}</ul>`;
			    } else {
			        detailsHtml += `<p>Keine Einrichtungen an diesem Standort.</p>`;
			    }
			
			    break;
    }

    content.innerHTML = detailsHtml;
    
    // Hintergrundfarbe basierend auf dem Typ einstellen
/*
    modal.classList.remove('person-modal', 'facility-modal', 'location-modal');
    if (type === 'person') {
        modal.classList.add('person-modal');
    } else if (type === 'facility') {
        modal.classList.add('facility-modal');
    } else if (type === 'location') {
        modal.classList.add('location-modal');
    }
*/


    // Hintergrundfarbe basierend auf dem Typ einstellen
    content.classList.remove('person-modal', 'facility-modal', 'location-modal');
    if (type === 'person') {
        content.classList.add('person-modal');
        if (itemData.department) {
            const color = getDepartmentColor(itemData.department);
            content.style.borderRight = `8px solid ${color}`;
        } else {
            content.style.borderRight = '';
        }
    } else if (type === 'facility') {
        content.classList.add('facility-modal');
    } else if (type === 'location') {
        content.classList.add('location-modal');
    }

    
    modal.style.display = "block";


    // Event-Listener für die Links innerhalb des Modals
    content.querySelectorAll('a[data-type], a[data-filter]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.dataset.type) {
                const clickedType = e.target.dataset.type;
                const clickedId = parseInt(e.target.dataset.id);
                let newItemData;
                switch(clickedType) {
                    case 'person':
                        newItemData = data.persons.find(p => p.id === clickedId);
                        break;
                    case 'facility':
                        newItemData = data.facilities.find(f => f.id === clickedId);
                        break;
                    case 'location':
                        newItemData = data.locations.find(l => l.id === clickedId);
                        break;
                }
                if (newItemData) {
                    showDetails(clickedType, newItemData);
                }
            } else if (e.target.dataset.filter) {
                const filterType = e.target.dataset.filter;
                const filterValue = e.target.dataset.value;
                if (filterType === 'language') {
                    renderData('person', filterValue);
                } else if (filterType === 'language-level') {
                    renderData('person', null, filterValue);
                }
                modal.style.display = "none";
            }
        });
    });
}

function getLocationName(id) {
    return data.locations.find(l => l.id === id)?.name || 'Unbekannt';
}

function getFacilityName(id) {
    return data.facilities.find(f => f.id === id)?.name || 'Unbekannt';
}

function getDepartmentName(id) {
    return data.departments.find(d => d.id === id)?.name || 'Unbekannt';
}

function getDepartmentColor(id) {
    return data.departments.find(d => d.id === id)?.color || '#888';
}

function getPersonsInFacility(facilityId) {
    return data.persons.map(person => {
        // Finde die Facility-Referenz für diese Einrichtung
        const facilityRef = person.facilities.find(facilityRef => facilityRef.facilityId === facilityId);
        if (facilityRef) {
            return {
                person: person,
                role: facilityRef.role
            };
        } else {
            return null;
        }
    }).filter(entry => entry !== null);
}

function getFacilitiesInLocation(locationId) {
    return data.facilities.filter(f => f.location === locationId);
}

function updateFilterDisplay(languageFilter, languageLevelFilter) {
    const filterDisplay = document.getElementById('currentFilter');
    if (languageFilter || languageLevelFilter) {
        let filterText = '';
        if (languageFilter) {
            filterText = `Sprache: ${languageFilter}`;
        } else if (languageLevelFilter) {
            const [lang, level] = languageLevelFilter.split('-');
            filterText = `Sprache: ${lang}, Niveau: ${level}`;
        }
        filterDisplay.textContent = `Aktueller Filter: ${filterText}`;
        filterDisplay.style.display = 'block';
    } else {
        filterDisplay.style.display = 'none';
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM fully loaded. Starting data load...");
    loadData();
//     updateHeaderWords();
    setInterval(updateHeaderWords, 300000); // Aktualisiert alle 5 Minuten
});

document.querySelector('.search-bar').addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    const filterType = document.getElementById('filterType').value;
    filterAndRenderData(searchTerm, filterType);
});

document.getElementById('filterType').addEventListener('change', (e) => {
    const searchTerm = document.querySelector('.search-bar').value.toLowerCase();
    filterAndRenderData(searchTerm, e.target.value);
});

document.querySelector('.close').addEventListener('click', () => {
    document.getElementById('detailModal').style.display = "none";
});

window.onclick = (event) => {
    if (event.target == document.getElementById('detailModal')) {
        document.getElementById('detailModal').style.display = "none";
    }
};