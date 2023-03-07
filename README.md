# AGBank
Criação de um resumido sistema bancário. Projeto da disciplina Programação Avançada em PHP pela Pós graduação na UFPE.

RUN NA APLICAÇÃO:
symfony server:start


RUN DO BANCO DE DADOS DOCKER:
docker compose up


Carregar dados fictícios criados em DataFixtures/AppFixtures.php:
symfony console doctrine:fixtures:load


Para fazer login na aplicação como administrador:
admin@admin.com
123

Para fazer login na aplicação como usuário:
usuario1@agbank.com
123

Para fazer login na aplicação como gerente:
gerente1@agbank.com
123


Comando extra para limpar o cache do navegador:
symfony console cache:clear --env=dev --no-warmup
ou
php bin/console cache:clear 


Para desfazer a última migration:
symfony console doctrine:migrations:migrate prev


