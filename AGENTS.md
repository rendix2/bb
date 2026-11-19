# AGENTS.md – bb (fórum v Nette)

Paměť pro AI agenty (Claude Code). Podrobná dokumentace je v Obsidianu:
`/mnt/c/obsidian/homelab/Homelab/Projekty/BB/` (index `_BB.md`).

## Co to je
Diskusní fórum (styl phpBB: kategorie → fóra → témata → příspěvky, PM, ankety, poděkování,
hlášení, ranky, avatary, moderace, admin). Historie od 2018 (dibi + MySQL), od 2026 probíhá
**přepis na Doctrine (nettrine) + PostgreSQL + nová UI struktura**.

## Prostředí
- Docker: `docker compose up -d`, služba **`bb-web`** (PHP 8.4 Apache, port 9080), `bb-postgres` (5432),
  `bb-mysql` (4306, legacy), `bb-redis`, `bb-adminer` (9082). Příkazy: `docker compose exec -T bb-web …`.
- Větev `master`, `composer.lock` je verzovaný, `package-lock.json` je v repu taky.
- ⚠️ `make all` / `make dirs-build` → `create-dirs` **smaže `vendor`, `node_modules`, `temp`**. Nepouštět bez potřeby.
- `make db` = drop celé DB + migrace + fixtures (destruktivní).
- `make phpstan` (level 2 + strict rules + doctrine), `make rector`. Testy **neexistují** (žádné `tests/`).
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
- **Starý kód (mizí)**: `app/models` (dibi `Manager`/`CrudManager`, `#[Deprecated]`), `app/*Module/presenters`,
  `app/presenters`, `app/forms`, `app/controls`, `config/config.neon` (stará MySQL konfigurace, Bootstrap ho nenačítá).
  Commity „remove old model, update entity“ = postupná migrace.
- Repozitáře se registrují v `config/services.neon` přes `entityManagerDecorator::getRepository(...)`.

## Pravidla (navíc ke globálnímu ~/.claude/CLAUDE.md)
- Nový kód jen do nové struktury (`app/UI`, `app/Model`), DB přes Doctrine/QueryBuilder, ne dibi.
- Tajemství: `config/env/*.neon` obsahují přístupy – nevypisovat.
