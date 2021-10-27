#!/usr/bin/php8.0
<?php
/*
** Zabbix Agent Monitor Joomla
** https://github.com/bitts/zabbix_joomla
** Copyright (C) 2020-2021 Zabbix Monitor Agent SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**
** Create by Marcelo Valvassori BITTENCOURT <marcelo.valvassori@gmail.com>
** Github Project: https://github.com/bitts/zabbix_joomla/
** Brazil
**
** Versions
** 1.0v - 26/09/2021 - Primeira versão do monitor zabbix para joomla
** 1.1v - 17/10/2021 - Melhorias para funcionamento com regra de descoberta utilizando dados dos sites publicados pelo apache
** 1.2v - 27/10/2021 - Melhorias na busca de dados no arquivo de configuração do apache com base no retorno do comando apache2ctl
** 
** NOTES:
** Obs: Necessário habilitar acesso root para usuario Zabbix:
** https://www.zabbix.com/documentation/current/pt/manual/appendix/config/zabbix_agentd
** https://www.zabbix.com/documentation/4.0/manual/appendix/install/run_agent_as_root
** 
** USE:
** zabbix_agentd -t joomla.site[1cta,jm_version]
** 
*/

$url = $htmlstatus = '';
if(php_sapi_name() == 'cli' || PHP_SAPI === 'cli'){

	if(isset($argv[1])) {
		if(isset($argv[1]) && !isset($argv[2])){
			switch($argv[1]){
                        	case 'sites':
					header('Content-Type: application/json;charset=utf-8');
                                	$valor = publish_apache();
		                        //echo json_encode($valor, JSON_UNESCAPED_UNICODE);
					echo json_encode($valor, JSON_UNESCAPED_SLASHES);
					//echo json_encode($valor, JSON_PRETTY_PRINT);
                	        break;
				case 'lastversionjoomla':
					echo JoomlalastVersion();
				break;
			}
		}else if(isset($argv[1]) && isset($argv[2])){
			$site = $argv[1];
			$vh = parseVirtualHosts("/etc/apache2/sites-available/{$site}.conf");
	                $jm = new mn_joomla_zb($vh->documentRoot);
			if(!empty($vh) && !empty($jm)){
				switch($argv[2]){
					case 'sites':
						$valor = publish_apache();
						echo json_encode($valor, JSON_UNESCAPED_UNICODE);
					break;
					case 'virtualhosts':
						echo json_encode($vh);
					break;
					case 'folder':
						echo !empty($vh->documentRoot)?$vh->documentRoot:null;
					break;
					case 'serverName':
						echo !empty($vh->serverName)?$vh->serverName:null;
					break;
					case 'serverAlias':
						echo !empty($vh->serverAlias)?$vh->serverAlias:null;
					break;
					case 'port':
						echo !empty($vh->ports)?implode(',',$vh->ports):null;
					break;
					case 'apacheconf':
						echo !empty($vh->file)?$vh->file:null;
					break;
					case 'jpas':
						echo json_encode($jm->jm_backupfiles());
					break;
					case 'jpasize':
						echo $jm->jpa('jpasize');
					break;
					case 'hjpasize':
        	                        	echo $jm->jpa('hjpasize');
       		                        break;
					case 'njpas':
       	                                	echo $jm->jpa('njpas');
       	                         	break;
					case 'foldersize':
						echo $jm->foldersize();
					break;
					case 'hfoldersize':
						echo $jm->hfoldersize();
					break;
					case 'permission':
						echo substr(sprintf('%o', fileperms($vh->documentRoot)), -4);
					break;
					case 'jm_version':
						echo $jm->getJoomlaVersionCURL();
					break;
					case 'jm_lastversion':
						echo JoomlalastVersion();
					break;
					case 'jm_dataconfiguration':
						$item = isset($argv[3])?$argv[3]:'';
						$valor = $jm->jm_getParamConfiguration($vh->documentRoot,$item);
						unset($valor->dtbs);unset($valor->pswd);
						echo json_encode($valor, JSON_UNESCAPED_UNICODE);
					break;

					case 'jm_users':
						$param = $jm->jm_getParamConfiguration();
						$valor = $jm->getDBUsers($param);
						echo json_encode($valor, JSON_UNESCAPED_UNICODE);
					break;
					case 'jm_nusers':
       	                                	$param = $jm->jm_getParamConfiguration();
       	                                 	$valor = $jm->getDBUsers($param);
       	                                 	echo sizeof($valor->usuarios);
       	                         	break;

				}
			}else echo "Nãoo foi possível ler arquivo de configuração do apache para coleta dos dados";
		}
	}

}

/**
* return information to configuration publish apache2 website
* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
* @upgrade 27/10/2021 - nginx | apache
* @param String $file 
* @return mixed
*/
function parseVirtualHosts($file) {
	$obj = new stdClass();
	if(!file_exists($file))return $obj;
	else{
		try{
			$fh = fopen($file, 'r');
			$port = $documentRoot = $serverName = $serverAlias = '';
			$obj->file_conf = $file;
			if($fh){
				while(!feof($fh)) {
					$line = fgets($fh);
					if(!empty($line)){
						/* apache */
						if (empty($port) && preg_match("/<VirtualHost/i", $line) ) {
							preg_match("/<VirtualHost\s+(.+):(.+)\s*>/i", $line, $results);
							if( !empty($results[1]) && $results[1] == "*" && !empty($results[2]) ) {
								$obj->ports[] = $port = $results[2];
							}
						}
						/* nginx */
						if(empty($port) && preg_match('/listen/i', $line) ){
							if ( preg_match("/(\d+)/g", $line, $results)) {
								if (isset($results[0]) && !empty($results[1]) ) {
									$values = array_values(array_filter(explode(' ', $results[0])));
									if(!empty($values[1]))$obj->ports[] = $port = trim($values[1]);
								}
							}
						}

						/* apache */
						if (empty($obj->documentRoot) && preg_match("/DocumentRoot\s+(.+)\s*/i", $line, $results) ) {
							if (isset($results[0]) && !empty($results[1]) ) {
								$values = array_values(array_filter(explode(' ', $results[0])));
								if($values[0] == "DocumentRoot")$obj->documentRoot = $documentRoot = trim($values[1]);
							}
						}
						/* nginx */
						if (empty($obj->documentRoot) && preg_match("/root\s+(.+)\s*/i", $line, $results) ){
							if (isset($results[0]) && !empty($results[1]) ) {
								$values = array_values(array_filter(explode(' ', $results[0])));
								if($values[0] == "root")$obj->documentRoot = $documentRoot = trim($values[1]);
							}
						}
						/* apache */
						if (empty($serverName) && preg_match("/ServerName\s+(.+)\s*/i", $line, $results) ){
							if (isset($results[0]) && !empty($results[1]) ) {
								$values = array_values(array_filter(explode(' ', $results[0])));
								if(!empty($values[1]))$obj->serverName = $serverName = trim($values[1]);
							}
						}
						/* apache */
						if(empty($serverAlias) && preg_match("/ServerAlias\s+(.+)\s*/i", $line, $results) ){
							if (isset($results[0]) && !empty($results[1]) ) {
								$values = array_values(array_filter(explode(' ', $results[0])));
								if(!empty($values[1]))$obj->serverAlias = $serverAlias = trim($values[1]);
							}
						}
						/* nginx */
						if((empty($serverAlias) || empty($serverName)) && preg_match("/server_name\s+(.+)\s*/i", $line, $results)){
							if (isset($results[0]) && !empty($results[1]) ) {
								$values = array_values(array_filter(explode(' ', $results[0])));
								if(!empty($values[1]))$obj->serverName = $obj->serverAlias = $serverName = $serverAlias = trim($values[1]);
							}
						}
					}
				}
			}

		} catch (\Throwable $e) {
			echo "Erro ao buscar dados dos Virtual Hosts : {$e->getMessage()}";
		} finally {
			if ($fh) fclose($fh);
			if(!isset($obj->ports) || empty($obj->ports)) $obj->ports[] = 80;
		}
		return $obj;
	}
}

/**
* return status to active url
* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>	
* @param string $url 
* @return bool
*/    
function url_exists( $url ) {
	if( function_exists('curl_init') ){
		$ch = curl_init( $url );
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	}

	if( !isset($code) || empty($code) ){
		if(function_exists('get_headers') ){
			$file_headers = @get_headers($url);
			if ($file_headers === false) $code = 404;
			$code = substr($file_headers[0], 9, 3);
		}
	}
	return ($code >= 200 && $code < 400); // verifica se recebe "status OK"
}

/**
* return last version released to CMS Joomla!
* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
* @return string   
*/
function JoomlalastVersion(){
	$version = 0;
	if( function_exists('curl_init') &&  function_exists('json_decode') ){
		$json_server = "https://downloads.joomla.org/api/v1/latest/cms";
		if(url_exists($json_server)){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $json_server);
			curl_setopt($ch, CURLOPT_REFERER, $json_server);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$content = curl_exec($ch);
			curl_close($ch);
			$data = json_decode($content);
			if(!empty($data)){
				foreach ($data as $campo => $value){
					if( $campo == "branch" && $value == "Joomla! 3" ){
						$version = $campo->version;
					}
				}
			}
		}
	}
	if( @$version == 0 && function_exists('json_decode') && function_exists('file_get_contents') ){
		$json_server = "https://downloads.joomla.org/api/v1/latest/cms";
		if(url_exists($json_server)){
			$arrContextOptions=array(
				"ssl" => array(
					"verify_peer"=>false,
					"verify_peer_name"=>false
				)
			);
			$content = file_get_contents($json_server, false, stream_context_create($arrContextOptions));
			$data = json_decode($content);
			if(!empty($data)){
				foreach ($data as $campo => $value){
					if( $campo == "branch" && $value == "Joomla! 3" ){
						$version = $campo->version;
					}
				}
			}
		}
	}
	if( $version == 0 && function_exists('simplexml_load_file') ){
		$xml_server = "http://update.joomla.org/core/list.xml";
		if(url_exists($xml_server)){
			$xml = simplexml_load_file($xml_server);
			if($xml){
				foreach ($xml->children() as $campo => $value) {
					foreach ($value->attributes() as $k=>$v) {
						if($k == "version")$version = $v;
					}
				}
			}
		}
	}
	if($version == 0)$version="4.0.3";
	return $version;
}

/**
* return websites to avaliables in apache server
* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
* @upgrade 27/10/2021 - checked published sites using apache2ctl | nginx? future!
* @return object   
*/
function publish_apache($path = "/etc/apache2/sites-available/"){
	$retorno = new stdClass();

        if(function_exists('exec')){
		/*
		# apache2ctl -S   [On Debian/Ubuntu]
		# apachectl -S    [On CentOS/RHEL]
		OR
		# httpd -S
		*/
                $out = "";
                $cmd="apache2ctl -S | grep namevhost | awk -F ' ' '{ print $4 }'";
                $out = array();
                exec($cmd, $out);
                if(!empty($out)){
                        foreach($out as $site){
                                $obj = new stdClass();
                                $obj->om = $site;
                                $retorno->data[] = $obj;
                        }
                }
        }

        try{
                $diretorio = dir($path);
                while($vhost_conf = $diretorio->read()){
                        $site = str_replace('.conf','',$vhost_conf);
                        if(!empty($site) && (!empty($published)?in_array($site, $published):true) ){
                                //$retorno->data[] = json_decode('{"{#OM}": "'. $site . '"}');
                                //$retorno->data[] = json_decode("{'om' : $site}");
                                $obj = new stdClass();
                                $obj->om = $site;
                                $retorno->data[] = $obj;
                        }
                }
        }catch(Exception $e){
                echo "Erro ao coletar dados dos sites hospedados : {$e->getMessage()}";
        } finally {
                $diretorio->close();
        }

        return $retorno;
}


/**
* Return information about application CMS Joomla!
* Application: Zabbix Agent Monitor UserParameter
* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
*/

class mn_joomla_zb{
  
	public function __construct($folder){
		$this->folder = $folder;
	}

	/**
	* format size length bytes
	* @access private
	* @param int $size 
	* @return int
	*/
	private function format_size($size) {
  		$mod = 1024;
  		$units = explode(' ','B KB MB GB TB PB');
  		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
  		}
  		return round($size, 2) . ' ' . $units[$i];
	}
	
	/**
	* search files and folder to path 
	* @access private
	* @param string $path 
	* @return array 
	*/
	private function iterate_dir($path) {
		$files = array( );
		if (is_dir($path) && is_readable($path)) {
			$dir = dir($path);
			while (false !== ($file = $dir->read( ))) {
				// skip . and ..
				if (('.' == $file) || ('..' == $file)) {
					continue;
				}
				if (is_dir("$path/$file")) {
					$files = array_merge($files, self::iterate_dir("$path/$file"));
				} else {
					array_push($files, "$path/$file");
				}
			}
			$dir->close( );
		}
		return $files;
	}

	/**
	* return size of folder 
	* @access private
	* @param string $folder 
	* @return int 
	*/  
	public function foldersize($folder = "") {
		$path = isset($folder) && !empty($folder)?$folder:$this->folder;
  		$total_size = 0;
		if( function_exists('scandir') ){
			$files = @scandir($path);
			if($files){
				foreach($files as $t) {
					if (is_dir(rtrim($path, '/') . '/' . $t)) {
						if ($t<>"." && $t<>"..") {
							$size = $this->foldersize(rtrim($path, '/') . '/' . $t);
							$total_size += $size;
						}
					} else {
						$size = filesize(rtrim($path, '/') . '/' . $t);
						$total_size += $size;
					}
				}
			}
		}else echo "Não habilitado scandir do PHP necessário para verificar tamanho da pasta {$path} ";
  		return $total_size;
	}

	
	/**
	* return sizeof the folder
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access private
	* @param string $folder	
	* @return string   
	*/
	public function hfoldersize($folder = ""){
		$path = isset($folder) && !empty($folder)?$folder:$this->folder;
		return $this->format_size($this->foldersize($path));
	}

	/**
	* return object content information to backup files to CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access private
	* @param string $folder
	* @return mixed     
	*/
	public function jm_backupfiles($folder = ""){
		$jpas = array();
		$pasta = isset($folder) && !empty($folder)?$folder:$this->folder;
		$files = self::iterate_dir($pasta);
		foreach ($files as $file) {
			if(is_file($file))
				if( preg_match('~\.(jpa|j01|j02|j03|j04|j05|j06|j07|j07|j08|j09|j10|j11|j12|j13|j14|j15|j16)$~', $file) ){
					$oF = new stdClass();
					$oF->tamanho = filesize($file);
					$oF->size = $this->format_size(filesize($file));
					$oF->name = (string)$file;
					$oF->date = date('Y-m-d G:i:s', filemtime($file) );
					$jpas[] = $oF;
				}
		}
		return $jpas;
	}

	/**
	* return last version stable CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access private
	* @param string $folder   
	* @return string   
	*/
	public function jm_getVersion($folder = ""){
		$path = isset($folder) && !empty($folder)?$folder:$this->folder;
		$vs = 0;
		$version = $vs;
		if( function_exists('simplexml_load_file') ){
			if(file_exists("{$path}/administrator/manifests/files/joomla.xml")){
				$xml = simplexml_load_file("{$path}/administrator/manifests/files/joomla.xml");
				$version = (string)$xml->version;
			}else if(file_exists("{$path}/language/pt-BR/pt-BR.xml")){
				$xml = simplexml_load_file("{$path}/language/pt-BR/pt-BR.xml");
				$version = (string)$xml->version;
			}else if(file_exists("{$path}/language/en-GB/en-GB.xml")){
				$xml = simplexml_load_file("{$path}/language/en-GB/en-GB.xml");
				$version = (string)$xml->version;
			}else {
				$version = $this->getJoomlaVersionCURL($path);
			}
		}
		return $version;
	}

	/**
	* return last version stable CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access private
	* @param string $folder   
	* @return string   
	*/
	public function getJoomlaVersionCURL($folder = "") {
		$path = isset($folder) && !empty($folder)?$folder:$this->folder;
	 	$version = "";
		//1.5.x | 1.6.x | 1.7.x
		$F1 = "{$path}/libraries/joomla/version.php" ;
		$F2 = "{$path}/libraries/cms/version/version.php" ;
		$F3 = "{$path}/includes/version.php" ;
		$F4 = "{$path}/libraries/src/Version.php" ;

		$jversion = 0;

		$url = (file_exists($F1)?$F1:(file_exists($F2)?$F2:(file_exists($F3)?$F3:(file_exists($F4)?$F4:''))));
		if($url){
			$html = "";
			if( function_exists('curl_init') ){
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

				//get HTML from url
				$html = curl_exec($ch);
				curl_close($ch);
			}
			if( empty($html) && function_exists('file_get_contents') ){
				$html = file_get_contents( $url );
			}

			if(!empty($html)){
				$doc = new DOMDocument();
				libxml_use_internal_errors(true);		//suppress HTML warnings
				$doc->loadHTML($html);
				libxml_clear_errors();

				$metas = $doc->getElementsByTagName('meta');
				for ($i = 0; $i < $metas->length; $i++)	{
					$meta = $metas->item($i);
					if($meta->getAttribute('name') == 'jversion')
						$jversion = $meta->getAttribute('content');
				}
			}
		}
		if($jversion !== 0){
			$variables = trim(str_replace(array('<?php', '?>', 'class', 'JConfig', '{', '}', 'public', 'private','\''), '', file_get_contents($url)));
			if(!empty($variables)){
				//$vara = preg_replace('/\s+/','',$variables);
				//$vara = $variables;

				$pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
				$vara = preg_replace($pattern, '', $variables);

				$vara = preg_replace(array('/\s+/','/const/'),'',$vara);


				if(!empty($vara)){
					$varb = explode(';', $vara);
					foreach($varb as $varc){
						$vard = explode('=', $varc);
						if(!empty($vard)){
							if($vard[0] == 'RELEASE')$jversion = $vard[1];
							if($vard[0] == 'DEV_LEVEL')$jversion .= ".{$vard[1]}";
						}
					}
				}
			}
		}
		
		return $jversion;
 	}

	/**
	* return information content in configuration.php file to CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access private
	* @param string $folder   
	* @param string $parm |host,pswd,dtbs,user,dbtype,dbprefix,mailfrom,fromname,smtphost,sitename,editor  
	* @return string|object   
	*/
	public function jm_getParamConfiguration( $folder = '', $parm = '' ){
		$path = !empty($folder)?$folder:$this->folder;
		$dtconf = new stdClass();
		if(!empty( $path )){
			$config = "{$path}/configuration.php";
			if(file_exists($config)){
				$variables = trim(str_replace(array('<?php', '?>', 'class', 'JConfig', '{', '}', 'public', 'private','\''), '', file_get_contents($config)));
				if(!empty($variables)){
					$vara = preg_replace('/\s+/','',$variables);
					//$vara = trim($variables);
					if(!empty($vara)){
						$varb = explode(';', $vara);
						foreach($varb as $varc){
							$vard = explode('=', $varc);
							if(!empty($vard)){
								if($vard[0] == '$host')$dtconf->host = $vard[1];
								if($vard[0] == '$password')$dtconf->pswd = $vard[1];
								if($vard[0] == '$db')$dtconf->dtbs = $vard[1];
								if($vard[0] == '$user')$dtconf->user = $vard[1];
								if($vard[0] == '$dbtype')$dtconf->dbtype = $vard[1];
								if($vard[0] == '$dbprefix')$dtconf->dbprefix = $vard[1];
								if($vard[0] == '$mailfrom')$dtconf->mailfrom = $vard[1];
								if($vard[0] == '$fromname')$dtconf->fromname = $vard[1];
								if($vard[0] == '$smtphost')$dtconf->smtphost = $vard[1];
								if($vard[0] == '$sitename')$dtconf->sitename = $vard[1];
								if($vard[0] == '$editor')$dtconf->editor = $vard[1];
							}
						}
					}
				}
			}
		}
		if(!empty($parm) && !empty($dtconf)){
			foreach($dtconf as $campo => $valor){
				if($campo == $parm)return $valor;
			}
		}else return $dtconf;
	}

	/**
	* return users insert in database/table __users to CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access public
	* @param string $parm | dtbs,dbprefix,dbtype,host,pswd,user
	* @return mixed   
	*/
	public function getDBUsers( $parm ){
		$Obj = new stdClass();
		if(!empty($parm)){
			/*
			[dtbs] => internet_badmgusm
			[dbprefix] => jmla
			[dbtype] => mysqli
			[editor] => codemirror
			[fromname] => Site
			[host] => 192.168.1.1
			[mailfrom] => informatica@site.com
			[pswd] => 
			[sitename] => 
			[smtphost] => localhost
			[user] => web_site
			*/
			if(!empty($Obj) && function_exists('mysql_connect')){
				try{
					if(isset($parm->host) && !empty($parm->host) && isset($parm->user) && !empty($parm->user) && isset($parm->pswd) && !empty($parm->pswd) && isset($parm->dtbs) && !empty($parm->dtbs)){
						$link = @mysql_connect($parm->host,$parm->user,$parm->pswd);
						$dtbs = @mysql_select_db($link,$parm->dtbs);
						if($link && $dtbs){
							$SQL = "SELECT name, username, email FROM {$parm->dbprefix}users";
							$result = mysql_query($SQL);
							while ($row = mysql_fetch_object($result)) {
								$Obj->usuarios[] = $row;
							}
						}
					}
				}catch(Exception $e){
					 $Obj->error[] = $e->getMessage();
				}
			}
			if(!empty($Obj)) {
				try{
					$conn = new PDO("mysql:host={$parm->host};dbname={$parm->dtbs};charset=utf8", $parm->user, $parm->pswd);
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					if($conn){
						$SQL = "SELECT name, username, email FROM {$parm->dbprefix}users";
						$stmt = $conn->query($SQL);
						if($stmt){
							while ($row = $stmt->fetchObject()) {
								$Obj->usuarios[] = $row;
							}
						}
					}else $Obj->erro[] = "Não foi possível estabelecer conexão com a base de dados da aplicação.";
				}catch(Exception $e){
					 $Obj->error[] = $e->getMessage();
				}

			}
		}
		return $Obj;
	}

	/**
	* return information about do backups files in format JPA to CMS Joomla!
	* @author Marcelo Valvassori Bittencourt <marcelo.valvassori@gmail.com>
	* @access public
	* @param string $parm | jpa,jpasize,hjpasize,njpas
	* @return mixed   
	*/
	public function jpa($retorno){
		$jpas = $this->jm_backupfiles();
		switch($retorno){
			case 'jpas':
				return json_encode($jpas);
			break;
			case 'jpasize':
				$tamanho = 0;
				foreach($jpas as $jpa){
				$tamanho += $jpa->tamanho;
						}
				return $tamanho;
			break;
			case 'hjpasize':
				$tamanho = 0;
				foreach($jpas as $jpa){
						$tamanho += $jpa->tamanho;
				}
				return $this->format_size($tamanho);
			break;
			case 'njpas':
				return sizeof($jpas);
			break;
		}
	}
}



