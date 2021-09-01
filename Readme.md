# Code video
Clone da netflix desenvolvido no curso de microsserviços da Fullcycle.

## Tecnlogias empregadas

Lista de tecnologias empregadas para o desenvolvimento do sistema.

- Docker e docker-compose
- Mysql 8.0
- PHP 8.0
- Laravel 8
- Nginx 1.21

## Como executar

Copiei o arquivo `.env.example` e renomeie `.env`, nele informe os dados de configuração que são compartilhados por todos os microserviços. São eles:

- DB_HOST: Nome do service do banco de dados no docker-compose;
- DB_DATABASE: Nome da base de dados que será utilizada pelo sistema;
- DB_PORT: Porta da base de dados que será utilizada pelo sistema;
- DB_USER: Usuário de acesso ao banco de dados que será utilizado pelo sistema;
- DB_PASSWORD: Senha de acesso ao banco de dados que será utilizado pelo sistema;