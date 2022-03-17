Aby uruchomić program należy: 

a) Posiadać skonfigurowaną bazę danych i użytkownika MySQL.
b) Posiadać uruchomiony serwer Apache.
c) Posiadać PHP.
d) Wypakować pliki do folderu.
e) utworzyć plik konfiguracyjny (kopia pliku: .env.template) o nazwie ".env"
f) uzupełnić dane w pliku .env:
      - usunąć znak # przed DATABASE_URL;
      - podać login, hasło, adres i port, nazwę bazy, wersję mysql DATABASE_URL="mysql://login:hasło@adres:port/nazwabazy?serverVersion=wersjamysql"
      - uzupełnić dane konta smtp mailowego.
g) otworzyć konsolę CMD/BASH/etc.. i przejść do folderu z plikami aplikacji.

h) wpisać komendę composer install
i) wpisać komendę php bin/console make:migration;
j) wpisać komendę php bin/console doctrine:migrations:migrate
k) wpisać komendę php bin/console doctrine:fixtures:load

Aplikacja jest gotowa do użycia.
