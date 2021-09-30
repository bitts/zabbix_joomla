# Monitoramento Zabbix para CMS Joomla!


# Zabbix User Agent to CMS Joomla!

Sistema de monitoramento utilizando UserPatameter do Zabbix para aplicação Joomla.

## Utilização

Para utilização necessário habilitar execução do script como root editando o arquivo /etc/zabbix/zabbix_agentd.conf e modificando as diretivas AllowRoot e User
```
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

# Adiconando funcionando do script de monitoramento do CMS Joomla!
UserParameter=joomla.site[*],/etc/zabbix/zabbix_agentd.d/zabbix_joomla.php $1 $2 $3

```



Para testes no console
```
zabbix_agentd -t joomla.site[{$site},opcao]
```
Onde {$site} é a Macro com o nome do Host do CMS a ser monitorado


# Opções para monitoramento

- Retorna informações do Virtual Host (arquivo de configuração do apache2 localizado em /etc/apache2/sites-avaliables/)
```
zabbix_agentd -t joomla.site[{$site},virtualhosts]
```

- Retorna path de localização dos arquivos do portal
```
zabbix_agentd -t joomla.site[{$site},folder]
```

- Retorna o nome do Host Virtual
```
zabbix_agentd -t joomla.site[{$site},serverName]
```

- Retorna Alias do Virtual Server
```
zabbix_agentd -t joomla.site[{$site},serverAlias]
```

- Retorna 
```
zabbix_agentd -t joomla.site[{$site},port]
```

- Retorna configurações do apache
```
zabbix_agentd -t joomla.site[{$site},apacheconf]
```
- Retorna os arquivos JPAs contidos no diretório e informações sobre esses arquivos
```
zabbix_agentd -t joomla.site[{$site},jpas]
```

- Retorna o tamanho total dos JPAs contidos no diretório Joomla
```
zabbix_agentd -t joomla.site[{$site},hjpasize]
```

- Retorna o numero total de JPAs contidos no diretório Joomla

```
zabbix_agentd -t joomla.site[{$site},njpas]
```

- Retorna o tamanho total ocupado pelos arquivos Joomla
```
zabbix_agentd -t joomla.site[{$site},foldersize]
```

- Retorna o tamanho total ocupado pelos arquivos Joomla (formatado)
```
zabbix_agentd -t joomla.site[{$site},hfoldersize]
```

- Retorna as permissões do arquivos Joomla
```
zabbix_agentd -t joomla.site[{$site},permission]
```

- Retorna a versão instalada do Joomla
```
zabbix_agentd -t joomla.site[{$site},jm_version]
```

- Retorna a última versão estável e liberada do CMS Joomla
```
zabbix_agentd -t joomla.site[{$site},jm_lastversion]
```

- Retorna as informações contidas no arquivo configuration.php do Joomla com algumas propriedades
```
zabbix_agentd -t joomla.site[{$site},jm_dataconfiguration]
```

- Retorna os usuarios cadastrados no banco de dados da aplicação com acesso administrativo
```
zabbix_agentd -t joomla.site[{$site},jm_users]
```
