DO PRACY
/** 
https://fs.siteor.com/ecdl/files/RODZAJE_BAZ_DANYCH_I_ICH_BUDOWA.pdf?1289369360
**/
(1) Wstęp

Tworząc system na wzór popularnych serwisów typu cloud jak (google drive)(dropbox) Na sam początek zbadałem rynek i porównałem serwisy do siebie.

(1.2)

systemu podzieliłem na trzy części i pokrótce postaram się je opisać.
użytkowników, plików, administracji

(1.2.1)
	Użytkownicy.
	czyli rejestrację do systemu,
	role użytkowników w systemie,
	udostępnianie zasobów użytkownikowi lub grupie użytkowników.

(1.2.2)
	Pliki.
	Upload pliku/plików,
	kwestia zabespieczenia - nie chcemy aby ktoś wrzucał nieznane formaty plików, w tym wirusy,
	foldery
	
(1.2.3)
	Administracja
	Zmiana limitów dysku dla użytkowników,
	Konfiguracja konta mailowego.
	przegląd zajętości dysku.
	panel administracyjny
	
(2) PHP Framework Baza danych

(2.1)Php

Jako język programowania wybrałem PHP. Głównym aspektem jest fakt, że używam go na codzień w pracy. PHP posiada bardzo obszerną dokumentację, jest ona przejrzysta i czytelna.

(2.2)Framework

Aby znacznie przyspieszyć proces tworzenia oprogramowania, wykorzystam w tym celu framework Symfony. Jest to jeden z popularniejszych frameworków. Będzie to mój pierwszy projekt przy użyciu Symfony. Na ten moment mogę dodać, że proces instalacji i tworzenie środowiska było bardzo proste. Zauważyłem że Symfony jest bardzo rozbudowane, całkiem łatwo jest znaleźć konkretną frazę w dokumentacji. Natomiast nie znając słowa kluczowego, ciężko jest rozeznać się w niej i znaleźć konkretną pomoc. Wyszukiwarka istnieje, nie zawsze pomaga.

(2.3)Baza Danych

Symfony na starcie oferuje zestaw narzędzi "orm-pack" w którym znajdują się trzy możliwości związane z wyborem bazy danych. -sqlite-mysql-postgresql, jako że na codzień używam systemu Windows użyłem popularnego narzędzia XAMPP, które posiada bazę danych MYSQL. Aby nie utrudniać sobie zadania użyłem mysql.
