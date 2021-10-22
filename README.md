# Monitoramento Zabbix para CMS Joomla!


<a href="https://github.com/bitts/zabbix_joomla"><img alt="GitHub release (latest by date)" src="https://img.shields.io/github/v/release/bitts/zabbix_joomla"></a>
<img alt="GitHub last commit" src="https://img.shields.io/github/last-commit/bitts/zabbix_joomla">
<img alt="GitHub Release Date" src="https://img.shields.io/github/release-date/bitts/zabbix_joomla">
<!-- img alt="GitHub all releases" src="https://img.shields.io/github/downloads/bitts/zabbix_joomla/total" -->
<!-- img alt="GitHub release (latest by date)" src="https://img.shields.io/github/downloads/bitts/zabbix_joomla/1.1v/total" -->
<img alt="GitHub repo size" src="https://img.shields.io/github/repo-size/bitts/zabbix_joomla">
<!-- img alt="Packagist License (custom server)" src="https://img.shields.io/packagist/l/bitts/zabbix_joomla" -->

<p align="left"><a href="https://downloads.joomla.org" target="_blank" rel="noopener noreferrer"><img src="https://downloads.joomla.org/images/homepage/joomla-logo.png" alt="Joomla Logo"></a></p>

- [Referências](#refer%C3%AAncias)
- [Template](#template)
- [Utilização](#utiliza%C3%A7%C3%A3o)
  * [Opções para monitoramento](#op%C3%A7%C3%B5es-para-monitoramento)
    + [virtualhosts](#virtualhosts)
    + [folder](#folder)
    + [serverName](#serverName)
    + [serverAlias](#serverAlias)
    + [port](#port)
    + [apacheconf](#apacheconf)
    + [jpas](#jpas)
    + [hjpasize](#hjpasize)
    + [foldersize](#foldersize)
    + [hfoldersize](#hfoldersize)
    + [permission](#permission)
    + [jm_version](#jm_version)
    + [jm_lastversion](#jm_lastversion)
    + [jm_dataconfiguration](#jm_dataconfiguration)
    + [jm_users](#jm_users)
    + [jm_nusers](#jm_nusers)

# Zabbix User Agent to CMS Joomla!

Script de monitoramento utilizando UserPatameter do Zabbix para aplicação Joomla.

## Referências
- https://www.zabbix.com/documentation/current/pt/manual/concepts/agent
- https://www.zabbix.com/documentation/guidelines/coding/php

## Template
- Import `zbx_export_templates_joomla.xml`.  [zabbix doc](https://www.zabbix.com/documentation/current/manual/xml_export_import/templates)
- Link template to hosts.  [zabbix doc](https://www.zabbix.com/documentation/current/manual/config/templates/linking)

## Utilização

Para utilização necessário habilitar execução do script como root editando o arquivo /etc/zabbix/zabbix_agentd.conf e modificando as diretivas AllowRoot e User
```bash
### Option: AllowRoot
#       Allow the agent to run as 'root'. If disabled and the agent is started by 'root', the agent
#       will try to switch to the user specified by the User configuration option instead.
#       Has no effect if started under a regular user.
#       0 - do not allow
#       1 - allow
#
# Mandatory: no
# Default:
# AllowRoot=0
AllowRoot=1

### Option: User
#       Drop privileges to a specific, existing user on the system.
#       Only has effect if run as 'root' and AllowRoot is disabled.
#
# Mandatory: no
# Default:
# User=zabbix
User=root

# Adicionando funcionando do script de monitoramento do CMS Joomla!
UserParameter=joomla.site[*],/etc/zabbix/zabbix_agentd.d/zabbix_joomla.php $1 $2 $3

```


Para testes no console
```bash
zabbix_agentd -t joomla.site[{$site},opcao]
```
Onde {$site} é a Macro com o nome do Host do CMS a ser monitorado


# Opções para monitoramento

## virtualhosts
Retorna informações do Virtual Host (arquivo de configuração do apache2 localizado em /etc/apache2/sites-avaliables/)
```bash
zabbix_agentd -t joomla.site[{$site},virtualhosts]
```
 > Output:
```js
{
  "ports":["80","443"],
  "file":"\/etc\/apache2\/sites-available\/site.com.br.conf",
  "documentRoot":"\/var\/www\/site\/",
  "serverName":"www.site.com.br",
  "serverAlias":"site.com.br"
}
```

## folder
Retorna path de localização dos arquivos do portal
```bash
zabbix_agentd -t joomla.site[{$site},folder]
```
 > Output:
```
  /var/www/site/
```

## serverName
Retorna o nome do Host Virtual
```bash
zabbix_agentd -t joomla.site[{$site},serverName]
```
 > Output:
```
  www.site.com.br
```

## serverAlias
Retorna Alias do Virtual Server
```bash
zabbix_agentd -t joomla.site[{$site},serverAlias]
```
 > Output:
```
  site.com.br
```

## port
Retorna Porta(s) utilizadas para publicação do site
```bash
zabbix_agentd -t joomla.site[{$site},port]
```
 > Output:
```
  80,443
```

## apacheconf
Retorna configurações do apache
```bash
zabbix_agentd -t joomla.site[{$site},apacheconf]
```
 > Output:
```
  /etc/apache2/sites-available/site.com.br.conf
```

## jpas
Retorna os arquivos JPAs contidos no diretório e informações sobre esses arquivos
```bash
zabbix_agentd -t joomla.site[{$site},jpas]
```
 > Output:
```js
[
  {
    "tamanho":554543,
    "size":"541.55 KB",
    "name":"\/var\/www\/1bcom\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie.jpa",
    "date":"2021-09-01 11:02:56"
  },
  {
    "tamanho":27120,
    "size":"26.48 KB",
    "name":"\/var\/www\/1bcom\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie-joomla.jpa",
    "date":"2021-09-01 11:02:56"
  },
  {
    "tamanho":46692,
    "size":"45.6 KB",
    "name":"\/var\/www\/1bcom\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie-mautic.jpa",
    "date":"2016-09-19 11:31:24"
  },
]
```

## hjpasize
Retorna o tamanho total dos JPAs contidos no diretório Joomla
```bash
zabbix_agentd -t joomla.site[{$site},hjpasize]
```
 > Output:
```
  628355
```

## njpas
Retorna o numero total de JPAs contidos no diretório Joomla
```bash
zabbix_agentd -t joomla.site[{$site},njpas]
```
 > Output:
```
  3
```

## foldersize
Retorna o tamanho total ocupado pelos arquivos Joomla
```bash
zabbix_agentd -t joomla.site[{$site},foldersize]
```
 > Output:
```
  623471397
```

## hfoldersize
Retorna o tamanho total ocupado pelos arquivos Joomla (formatado)
```bash
zabbix_agentd -t joomla.site[{$site},hfoldersize]
```
 > Output:
```
  594.59 MB
```

## permission
Retorna as permissões do arquivos Joomla
```bash
zabbix_agentd -t joomla.site[{$site},permission]
```
 > Output:
```
  2770
```

## jm_version
Retorna a versão instalada do Joomla
```bash
zabbix_agentd -t joomla.site[{$site},jm_version]
```
 > Output:
```
  3.9.27
```

## jm_lastversion
Retorna a última versão estável e liberada do CMS Joomla
```bash
zabbix_agentd -t joomla.site[{$site},jm_lastversion]
```
 > Output:
```
  4.0.3
```

## jm_dataconfiguration
Retorna as informações contidas no arquivo configuration.php do Joomla com algumas propriedades
```bash
zabbix_agentd -t joomla.site[{$site},jm_dataconfiguration]
```
 > Output:
```js
{
  "sitename":"MySite",
  "editor":"jce",
  "dbtype":"mysqli",
  "host":"localhost",
  "user":"web_site",
  "dbprefix":"myjml_",
  "mailfrom":"suporte@site.com.br",
  "fromname":"My Site",
  "smtphost":"smtp.site.com.br"
}
```

## jm_users
Retorna os usuarios cadastrados no banco de dados da aplicação com acesso administrativo
```bash
zabbix_agentd -t joomla.site[{$site},jm_users]
```
 > Output:
```js
{
  "usuarios":[
    {
      "name":"Super User",
      "username":"admin",
      "email":"admin@site.com.br"
    },
    {
      "name":"seguranca",
      "username":"seguranca",
      "email":"seguranca@site.com.br"
    },
    {
      "name":"Comunicação Social",
      "username":"comsoc",
      "email":"comsoc@site.com.br"
    },
  ]
}
```

## jm_nusers
Retorna os número total de usuarios cadastrados no banco de dados da aplicação com acesso administrativo
```bash
zabbix_agentd -t joomla.site[{$site},jm_nusers]
```
 > Output:
```
  3
```
