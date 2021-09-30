# Monitoramento Zabbix para CMS Joomla!


# Zabbix User Agent to CMS Joomla!

Sistema de monitoramento utilizando UserPatameter do Zabbix para aplicação Joomla.

## Utilização
```
zabbix_agentd -t joomla.site[{$site},opcao]
```
Onde {$site} é a Macro com o nome do Host do CMS a ser monitorado


# Opçoes para monitoramento

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
