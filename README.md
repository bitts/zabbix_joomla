# Zabbix monitoring for CMS Joomla!


<a href="https://github.com/bitts/zabbix_joomla"><img alt="GitHub release (latest by date)" src="https://img.shields.io/github/v/release/bitts/zabbix_joomla"></a>
<img alt="GitHub last commit" src="https://img.shields.io/github/last-commit/bitts/zabbix_joomla">
<img alt="GitHub Release Date" src="https://img.shields.io/github/release-date/bitts/zabbix_joomla">
<img alt="GitHub repo size" src="https://img.shields.io/github/repo-size/bitts/zabbix_joomla">
<!-- img alt="GitHub all releases" src="https://img.shields.io/github/downloads/bitts/zabbix_joomla/total" -->
<!-- img alt="GitHub release (latest by date)" src="https://img.shields.io/github/downloads/bitts/zabbix_joomla/1.1v/total" -->
<!-- img alt="Packagist License (custom server)" src="https://img.shields.io/packagist/l/bitts/zabbix_joomla" -->

<p align="left"><a href="https://downloads.joomla.org" target="_blank" rel="noopener noreferrer"><img src="https://downloads.joomla.org/images/homepage/joomla-logo.png" alt="Joomla Logo"></a></p>

- [References](#references)
- [Template](#template)
- [Usage](#usage)
  * [Options for monitoring](#options-for-monitoring)
    + [virtualhosts](#virtualhosts)
    + [folder](#folder)
    + [serverName](#serverName)
    + [serverAlias](#serverAlias)
    + [port](#port)
    + [apacheconf](#apacheconf)
    + [jpas](#jpas)
    + [hjpasize](#hjpasize)
    + [foldersize](#foldersize)
    + [hfolderssize](#hfolderssize)
    + [permission](#permission)
    + [jm_version](#jm_version)
    + [jm_lastversion](#jm_lastversion)
    + [jm_dataconfiguration](#jm_dataconfiguration)
    + [jm_users](#jm_users)
    + [jm_nusers](#jm_nusers)

# Zabbix User Agent to CMS Joomla!

Monitoring script using Zabbix UserPatameter for Joomla application.

## References
- https://www.zabbix.com/documentation/current/pt/manual/concepts/agent
- https://www.zabbix.com/documentation/guidelines/coding/php

## Template
- Import `zbx_export_templates_joomla.xml`. [zabbix doc](https://www.zabbix.com/documentation/current/manual/xml_export_import/templates)
- Link template to hosts. [zabbix doc](https://www.zabbix.com/documentation/current/manual/config/templates/linking)

## Usage

To use it, it is necessary to enable script execution as root by editing the /etc/zabbix/zabbix_agentd.conf file and modifying the AllowRoot and User directives
```bash
### Option: AllowRoot
# Allow the agent to run as 'root'. If disabled and the agent is started by 'root', the agent
# will try to switch to the user specified by the User configuration option instead.
# Has no effect if started under a regular user.
# 0 - do not allow
#1 - allow
#
# Mandatory: no
# Default:
# AllowRoot=0
AllowRoot=1

### Option: User
# Drop privileges to a specific, existing user on the system.
# Only has effect if run as 'root' and AllowRoot is disabled.
#
# Mandatory: no
# Default:
# User=zabbix
User=root

# Adding running CMS Joomla!
UserParameter=joomla.site[*],/etc/zabbix/zabbix_agentd.d/zabbix_joomla.php $1 $2 $3

```


For console testing
```bash
zabbix_agentd -t joomla.site[{$site},option]
```
Where {$site} is the Macro with the name of the CMS Host to be monitored


# Options for monitoring

## virtualhosts
Returns Virtual Host information (apache2 configuration file located in /etc/apache2/sites-avaliables/)
```bash
zabbix_agentd -t joomla.site[{$site},virtualhosts]
```
 > Output:
```js
{
  "ports":["80","443"],
  "file":"\/etc\/apache2\/sites-avaliable\/site.com.br.conf",
  "documentRoot":"\/var\/www\/site\/",
  "serverName":"www.site.com.br",
  "serverAlias":"site.com.br"
}
```

## folder
Return path of portal files location
```bash
zabbix_agentd -t joomla.site[{$site},folder]
```
 > Output:
```
  /var/www/site/
```

## serverName
Returns the name of the Virtual Host
```bash
zabbix_agentd -t joomla.site[{$site},serverName]
```
 > Output:
```
  www.site.com.br
```

## serverAlias
Return Virtual Server Alias
```bash
zabbix_agentd -t joomla.site[{$site},serverAlias]
```
 > Output:
```
  site.com.br
```

## port
Returns Port(s) used for site publication
```bash
zabbix_agentd -t joomla.site[{$site},port]
```
 > Output:
```
  80.443
```

## apacheconf
Return apache settings
```bash
zabbix_agentd -t joomla.site[{$site},apacheconf]
```
 > Output:
```
  /etc/apache2/sites-available/site.com.br.conf
```

## jpas
Returns the JPA files contained in the directory and information about those files
```bash
zabbix_agentd -t joomla.site[{$site},jpas]
```
 > Output:
```js
[
  {
    "size": 554543,
    "size":"541.55 KB",
    "name":"\/var\/www\/site\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie.jpa",
    "date":"2021-09-01 11:02:56"
  },
  {
    "size": 27120,
    "size":"26.48 KB",
    "name":"\/var\/www\/site\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie-joomla.jpa",
    "date":"2021-09-01 11:02:56"
  },
  {
    "size":46692,
    "size":"45.6 KB",
    "name":"\/var\/www\/site\/\/administrator\/components\/com_akeeba\/Master\/Installers\/angie-mautic.jpa",
    "date":"2016-09-19 11:31:24"
  },
]
```

## hjpasize
Returns the total size of JPAs contained in the Joomla directory
```bash
zabbix_agentd -t joomla.site[{$site},hjpasize]
```
 > Output:
```
  628355
```

## njpas
Returns the total number of JPAs contained in the Joomla directory
```bash
zabbix_agentd -t joomla.site[{$site},njpas]
```
 > Output:
```
  3
```

## foldersize
Returns the total size occupied by Joomla files
```bash
zabbix_agentd -t joomla.site[{$site},folderssize]
```
 > Output:
```
  623471397
```

## hfoldersize
Returns the total size occupied by Joomla (formatted) files
```bash
zabbix_agentd -t joomla.site[{$site},hfolderssize]
```
 > Output:
```
  594.59 MB
```

## permission
Return Joomla file permissions
```bash
zabbix_agentd -t joomla.site[{$site},permission]
```
 > Output:
```
  2770
```

## jm_version
Return installed version of Joomla
```bash
zabbix_agentd -t joomla.site[{$site},jm_version]
```
 > Output:
```
  3.9.27
```

## jm_lastversion
Returns the latest stable and released version of CMS Joomla
```bash
zabbix_agentd -t joomla.site[{$site},jm_lastversion]
```
 > Output:
```
  4.0.3
```

## jm_dataconfiguration
Returns the information contained in the Joomla configuration.php file with some properties
```bash
zabbix_agentd -t joomla.site[{$site},jm_dataconfiguration]
```
 > Output:
```js
{
  "sitename" : "MySite",
  "editor" : "jce",
  "dbtype" : "mysqli",
  "host" : "localhost",
  "user" : "web_site",
  "dbprefix" : "myjml_",
  "mailfrom" : "support@site.com.br",
  "fromname" : "My Site",
  "smtphost" : "smtp.site.com.br"
}
```

## jm_users
Returns users registered in the application's database with administrative access
```bash
zabbix_agentd -t joomla.site[{$site},jm_users]
```
 > Output:
```js
{
  "users":[
    {
      "name" : "Super User",
      "username" : "admin",
      "email" : "admin@site.com.br"
    },
    {
      "name" : "security",
      "username" : "security",
      "email" : "seguranca@site.com.br"
    },
    {
      "name" : "Social Communication",
      "username" : "comsoc",
      "email" : "comsoc@site.com.br"
    }
  ]
}
```

## jm_nusers
Returns the total number of users registered in the application's database with administrative access
```bash
zabbix_agentd -t joomla.site[{$site},jm_nusers]
```
 > Output:
```
  3
```
