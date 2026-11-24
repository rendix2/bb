# AGENTS.md – bb (fórum v Nette)

Paměť pro AI agenty (Claude Code). Podrobná dokumentace je v Obsidianu:
`/mnt/c/obsidian/homelab/Homelab/Projekty/BB/` (index `_BB.md`).

## Co to je
Diskusní fórum (styl phpBB: kategorie → fóra → témata → příspěvky, PM, ankety, poděkování,
hlášení, ranky, avatary, moderace, admin). Historie od 2018 (dibi + MySQL), od 2026 probíhá
**přepis na Doctrine (nettrine) + PostgreSQL + nová UI struktura**.

## Prostředí
- Docker: `docker compose up -d`, služba **`bb-web`** (PHP 8.4 Apache, http://localhost:9380), adminer 9382, `bb-postgres` 5732,
  `bb-redis` 6479 (MySQL zrušena v řezu 8a, 2026-11-25). Porty jsou zvolené tak, aby běžel zároveň s alfred/web-project/family-tree.
  Příkazy: `docker compose exec -T bb-web …`.
- Větev `master`, `composer.lock` je verzovaný, `package-lock.json` je v repu taky.
- ⚠️ `make all` / `make dirs-build` → `create-dirs` **smaže `vendor`, `node_modules`, `temp`**. Nepouštět bez potřeby.
- `make db` = drop celé DB + migrace + fixtures (destruktivní).
- `make phpstan` (level 2 + strict rules + doctrine), `make rector`.
- **Testy: Codeception jako web-project** (od 2026-11-19): `make unit|integration|functional|acceptance|test|coverage` (bez `dirs-build`, nic nemaže).
  Sady Unit (bez DB), Integration (DI kontejner + dev DB), Functional (PhpBrowser na `bb-web`), Acceptance (`selenium-chrome`, porty 4744/5903/7903).
  Převzaté konvenční testy: `tests/Unit/MigrationConventionsCest`, `tests/Integration/Schema/*`, `MigrationsDownUpCest`, `BigIntIdentifiersCest`,
  `ForeignKeyOnDeleteCest` (= cíl kroku 0: srovnat entity a migrace). Skupina `known-bug` (Make ji přeskakuje) = záměrně padající do opravy:
  `NoStaticMethodsOrTraitsCest` (Bootstrap, RouterFactory), `FlashMessageIsAnObjectCest`. `QueriesUseTheQueryBuilderCest` hlídá naostro od 7f (2026-11-24).
- Codestyle: `ruleset.xml` → **ninjify/coding-standard** (ne contributte/qa jako ostatní projekty); `composer qa` skript chybí.
- Konzole: `php bin/console` (contributte/console; migrations, fixtures, `database:create`).

## Architektura
- `app/Bootstrap.php` – kontext `web`/`console` (oddělené `temp/<ctx>`, `log/<ctx>`); načítá
  `config/common.neon`, `config/services.neon`, `config/env/{dev,test,prod}.neon`.
  ⚠️ `setDebugMode(true)` je natvrdo.
- Mapování presenterů: `App\UI\*\**Presenter` → **jen `app/UI/` je živé**. Router: `App\Core\RouterFactory`
  (`<presenter>/<action>/<uuid|id>`, výchozí `Web:Home`).
- **Nový kód**: `app/UI/<Modul>/<Entita>/<Akce>/` (presenter + `*Values` + DataGrid + `default.latte` + `Translations/*.cs|en.json`),
  `app/Model/Entity` (Doctrine atributy, `id` BIGINT + `uuid` Ramsey), `app/Model/Repository`, `app/Model/Facade`,
  `app/Database/{Migrations,Fixtures,Types}`, `app/Core` (authenticatory, router).
- **Starý kód smazán** (řez 8a, 2026-11-25): dibi, `app/models`, `app/*Module`, `app/presenters`, `app/controls`, `app/authorization`, staré neony.
  Z `app/forms` zůstávají jen kopie z web-project (`App\Forms`: `*FormControl*`, `IconBootstrapForm`, `Renderer`, `Mapper`).
- Repozitáře se registrují v `config/services.neon` přes `entityManagerDecorator::getRepository(...)`.

## Přepis (rozhodnutí 2026-11-19)
- **bb se píše znovu, ale funkčně zhruba jak to bylo.** Nový kód vzniká od nuly v `app/UI` nad Doctrine modelem podle konvencí web-project
  (nekopírovat a nepřevádět starý kód 1:1, git historie se neprochází).
- **Starý kód se NEMAŽE předem:** zůstává jako připomínka funkcí (co fórum umělo). Starou část smaž až ve chvíli, kdy ji nová stránka nahradí.

## Cíle modernizace (2026-11-19, detail v Obsidianu „2026-11-19 BB – cíle a analýza modernizace“)
- **Pořadí: nejdřív odstranit dibi** (převést starý kód na Doctrine do `app/UI` + `app/Model`, pak legacy smazat), teprve potom ostatní.
- bb nikde neběží, žádná produkční data: MySQL se jen maže, nic se nepřevádí.
- bb **sleduje web-project** (šablona, stejně jako alfred a family-tree), změny z ní se portují.
- Pryč s dibi, MySQL, traity a statickými metodami. Codestyle contributte/qa (společný `ruleset.xml` z web-project), PHPStan level 10, sladit s web-project (tests/, Makefile, latte-lint, konvenční testy).
- Dotazy: hybrid jako ve web-project. Jednoduché `find`/`findOneBy` přímo přes `$em->getRepository()`, repozitářová metoda jen pro QB s joiny/agregací, opakovaný dotaz nebo doménové pravidlo (s přesným `@return`). Žádné prázdné repozitáře ani wrappery `findByXId`/`findByX`.

## Pravidla (navíc ke globálnímu ~/.claude/CLAUDE.md)
- **Schéma (krok 0 hotový 2026-11-19):** entity popisují DB úplně (komentáře, id `BIGINT` → `int`, `#[JoinColumn]` s `nullable`/`onDelete`,
  `#[ManyToOne]` s `cascade: ['persist']`), hlídá `tests/Integration/Schema/*`. `onDelete`: CASCADE pro vše, co patří uživateli / obsahu;
  bez `onDelete` (NO ACTION) jen `users.language`, `category.parent`, `forum.parent`, `forum.category`.
  Skupiny nemají moderátora (`groups.moderator` zrušen 2026-11-24, fóra moderují `moderator_user`/`group_moderator`). Tabulky v množném čísle, kde je název klíčové slovo SQL (`users`, `groups`) - žádné uvozovky v `#[Table]`.
  Bez cyklů cizích klíčů (proto `topic` nemá `first_post_id`/`last_post_id`/`poll_id`). Soukromé zprávy = vlákna `private_message_*`.
  `cascade: 'remove'` na `ManyToMany` NIKDY (maže entity na druhé straně, ne vazební řádky).
- **Konce řádků LF** (`.gitattributes`: `* text=auto eol=lf`, repo převedeno 2026-11-19). Nové soubory piš s LF.
- **Migrace: 1 tabulka = 1 migrace** (Schema API, `public.` prefix, komentáře sloupců, indexy `K__Tabulka__sloupec`, FK `FK__Tabulka__sloupec`). Migrace se smí **upravovat zpětně** (nic neběží v produkci) – změnu tabulky dej do její zakládací migrace, ne do nové ALTER migrace; DB pak znovu `make db`.
- Nový kód jen do nové struktury (`app/UI`, `app/Model`), DB přes Doctrine/QueryBuilder, ne dibi.
- Tajemství: `config/env/*.neon` obsahují přístupy – nevypisovat.
