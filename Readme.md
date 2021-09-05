# Code video
Clone da netflix desenvolvido no curso de microsservi�os da Fullcycle.

## Tecnlogias empregadas

Lista de tecnologias empregadas para o desenvolvimento do sistema.

- Docker e docker-compose
- Mysql 8.0
- PHP 8.0
- Laravel 8
- Nginx 1.21

## Como executar

Copiei o arquivo `.env.example` e renomeie `.env`, nele informe os dados de configura��o que s�o compartilhados por todos os microservi�os. S�o eles:

- DB_HOST: Nome do service do banco de dados no docker-compose;
- DB_DATABASE: Nome da base de dados que ser� utilizada pelo sistema;
- DB_PORT: Porta da base de dados que ser� utilizada pelo sistema;
- DB_USER: Usu�rio de acesso ao banco de dados que ser� utilizado pelo sistema;
- DB_PASSWORD: Senha de acesso ao banco de dados que ser� utilizado pelo sistema;

Ap�s essa configura��o base, execute no terminal:
`docker-compose up -d` e ent�o ser� inicializado os servi�os.

## Executando Testes
Para executar os testes, acessar o terminal o servi�o catalog como comando `docker-compose exec catalog bash `, j� dentro do container, execute o comando `vendor/bin/phpunit`. Pronto, os testes ser�o executados!

OBS: Antes de realizar os testes, certifique-se que o servi�o de banco de dados j� est� dispon�vel para receber conex�es.

## Portas

Portas abertas para o host:
- Laravel: 8000

Portas na network:
- Mysql: 3306
- Nginx: 80
- PHP-FPM: 9000