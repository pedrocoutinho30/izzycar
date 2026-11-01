# Testes de fluxo em MySQL real

Testes de integração contra um MySQL 8 local, para o que o sqlite não cobre
(migrations só MySQL, `FIELD()`, concorrência com commits reais).

## Correr

    vendor/bin/phpunit -c phpunit.fluxo-mysql.xml

Requer MySQL em 127.0.0.1:3306 (root sem password) e a base `izzycar_fluxo_teste`.

## Criar / recriar a base

    mysql -uroot -h127.0.0.1 -e "DROP DATABASE IF EXISTS izzycar_fluxo_teste; CREATE DATABASE izzycar_fluxo_teste CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

(o `migrate:fresh` corre sozinho uma vez por processo.)

## Como funciona

- `phpunit.fluxo-mysql.xml` força todas as variáveis de BD/mail/queue (`force="true"`); nada vem do `.env`.
- O trait `Tests\Fluxo\Concerns\UsaMysqlIsolado` verifica driver mysql, host 127.0.0.1/localhost e base
  exatamente `izzycar_fluxo_teste` (config e `SELECT DATABASE()`) ANTES de migrar; senão aborta.
- Limpeza entre testes por `TRUNCATE` (sem transações), excepto `migrations`.
- Migrations que falhem em MySQL ficam em `UsaMysqlIsolado::$migrationsFalhadas` (não são corrigidas aqui).

## Avisos

- O `.env` do projeto aponta para PRODUÇÃO. Nunca correr estes testes com outra config nem apagar a guarda.
- O MySQL local tem outras bases: estes testes só tocam em `izzycar_fluxo_teste`.
- Esta suite não faz parte do `php artisan test` (o `phpunit.xml` só vê tests/Unit e tests/Feature).

## Achados: migrations vs MySQL 8.0.33 (migrate:fresh das 178)

O `migrate:fresh` rebenta; o trait recorre a correr ficheiro a ficheiro (173/178 aplicadas):

1. `2026_01_12_094536_add_consignment_page` — `pages.page_type_id` sem default (strict mode).
2. `2026_05_27_000001_create_vehicle_inspection_tables` — nome de FK com mais de 64 caracteres
   (`vehicle_inspection_categories_vehicle_inspection_template_id_foreign`).
3. `2026_05_27_000002_add_generated_from_inspection_id_to_v3_vehicles` — consequência de 2 (tabela inexistente).
4. `2026_08_05_110100_add_angariador_code_...` — coluna `clients.owner_id` ainda não existe (só criada por migration posterior).
5. `2026_08_05_150000_add_comprovativo_pagamento_...` — coluna `comissao_paga_em` ainda não existe.

`InfraSmokeTest::test_migracoes_aplicadas` fixa este conjunto.
