# AGBank
#### Criação de um resumido sistema bancário. Projeto da disciplina Programação Avançada em PHP pela Pós graduação na UFPE.

##### RUN NA APLICAÇÃO:
```console 
symfony server:start
```

##### RUN DO BANCO DE DADOS DOCKER:
```console 
docker compose up
```

##### Carregar dados fictícios criados em DataFixtures/AppFixtures.php:
```console 
symfony console doctrine:fixtures:load
```

##### Para fazer login na aplicação como
######  Administrador:
> admin@admin.com
> 123

###### Usuário:
> usuario1@agbank.com
> 123

##### Gerente:
> gerente1@agbank.com
> 123


##### Comando extra para limpar o cache do navegador:
```console 
symfony console cache:clear --env=dev --no-warmup
```
ou
```console
php bin/console cache:clear
``` 


##### Para desfazer a última migration:
```console 
symfony console doctrine:migrations:migrate prev
```

