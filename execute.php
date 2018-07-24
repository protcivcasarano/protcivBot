<?php
function conv_date ($data,$inverti=false)
{
  if($inverti)
  {
  list ($g, $m, $y) = explode ("-", $data);
  return "$y-$m-$g";
  }
  else
  {
  list ($y, $m, $d) = explode ("-", $data);
  return "$d-$m-$y";
  }
}

function se_iscritto ($chatid,$tpreturn='NOME')
{
	$chatid = trim($chatid);
	$urlCheck = 'http://www.protezionecivilecasarano.org/gestionale/api/check_reg.php?par='.$chatid;
	$json = file_get_contents($urlCheck);
	$obj = json_decode($json,true);
	$array = $obj[1];
	$cont = $obj[0]["conteggio"];
	$array2 = $array["name_array"];
	$data = $array2[0];
	$nome = $data["nome"];
	$cognome = $data["cognome"];
	
	if($cont == 0)
		return 'NO';
	else
	{
		if($tpreturn == 'NOMECOGNOME')
			return $nome.' '.$cognome;
		if($tpreturn == 'NOME')
			return $nome;
		if($tpreturn == 'COGNOME')
			return $cognome;
	}
	
}

function trova_parola($parola,$descrizione){ // Parola: la parola da cercare | Descrizione: frase in cui cercare
	$descrizione = preg_replace("/\W/", " ", $descrizione); // elimino caratteri speciali
	$des_cerca=explode(" ",$descrizione); // esplodo le singole parolo
	$risultato = count($des_cerca); // conto il totale delle parole esplose
	@$_ritono = false;
	for($i=0; $i<=$risultato; $i++){ // ciclo per fare controllo
		if(@$des_cerca[$i]==@$parola){
			@$_ritono = true; // se la trovo chiudo ciclo e ritorno l'ok
			break;
		}		
	}	
	return $_ritono;
}

function Sendmail($mittente, $destinatario, $soggetto, $bodyhtml, $bodytxt="", $allegato="", $allegatofolder="/public/"){
	$boundary1 ="XXMAILXX".md5(time())."XXMAILXX";
		$boundary2 ="YYMAILYY".md5(time())."YYMAILYY";
		if($bodytxt=="" && $bodyhtml!=""){
			$bodytxt=str_replace("<br>","\n",$bodyhtml);
			$bodytxt=strip_tags($bodyhtml);
		}
		if($bodytxt!="" && $bodyhtml==""){
			$bodyhtml=$bodytxt;
		}
		$headers = "From: $mittente\n";
		$headers .= "MIME-Version: 1.0\n";
		if ($allegato!=""){
			$headers .= "Content-Type: multipart/mixed;\n";
			$headers .= " boundary=\"$boundary1\";\n\n";
			$headers .= "--$boundary1\n";
		}
		$headers .= "Content-Type: multipart/alternative;\n";
		$headers .= " boundary=\"$boundary2\";\n\n";
		
		//mail alternativa solo testo
		$body = "--$boundary2\n";
		$body .= "Content-Type: text/plain; charset=ISO-8859-15; format=flowed\n";
		$body .= "Content-Transfer-Encoding: 7bit\n\n";
		$body .= "$bodytxt\n";
		//mail html
		$body .= "--$boundary2\n";
		$body .= "Content-Type: text/html; charset=ISO-8859-15\n";
		$body .= "Content-Transfer-Encoding: 7bit\n\n";
		$body .= "$bodyhtml\n\n";
		$body .= "--$boundary2--\n";
		//allegato se presente
		if ($allegato!=""){
			$fileallegato=getcwd().$allegatofolder.$allegato;
			$fp=@fopen($fileallegato, "r");
			if ($fp) {
				$data = fread($fp, filesize($fileallegato));	
			}
			$curr = base64_encode($data);
			
			$body .= "--$boundary1\n";
			$body .= "Content-Type: application/octet-stream;";
			$body .= "name=\"$allegato\"\n";
			$body .= "Content-Transfer-Encoding: base64\n\n";
			//$body .= "Content-Disposition: attachment;\n";
			//$body .= "filename=\"$allegato\"\n\n";	
			$body .= "$curr\n";
			$body .= "--$boundary1--\n";
		}
		
                if(@mail($destinatario, $soggetto, $body, $headers)) {
			return true;
                } else {
			return false;
                }
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

header("Content-Type: application/json");

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$senderId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$domanda = isset($message['text']) ? $message['text'] : "";

$domanda = trim($domanda);
$domandaL = trim(strtolower($domanda));
//$risposta = strtolower($domanda);
$risposta = trim('I tecnici sono a lavoro per migliorarmi in modo da farmi rispondere prima e più efficacemente alle tue domande o comandi, dovrai avere pazienza se ancora non capisco tutto quello che mi chiedi. Ti posso fornire la lista dei comandi se mi chiedi "aiuto"');


$listacomandi = '/aiuto - lista dei comandi 
/iscrizione - RISERVATO SOCI, insieme al codice tessera da accesso a dei servizi riservati ai soci
/iscriviavvisi - tutte le informazioni rilevanti sulla tua città e non solo
/contatti - visualizza i contatti dell\'associazione
/scrivi - manda un messaggio all\'associazione, facci sapere suggerimenti e commenti sul bot e sul nostro operato';

$contattiAss = 'Email: info@protezionecivilecasarano.org
Telefono: 08331855789
Fax: 08331850434
Cellulare: 3473912735
PEC: protezionecivilecasarano@pec.it

Puoi contattarci scrivendoci anche da qui. 

Scrivi: /scrivi testodelmessaggio. 

Esempio:
/scrivi ciao, vi segnalo un problema di rilevanza di protezione civile in via Bari. Un saluto da Mario Rossi';


$istr_allerameteo = 'Ecco come puoi usare il comando /allertameteo

/allertameteo ultima - Ti permettere di leggere le informazioni dell\'allerta corrente o appena passata.
/allertameteo iscrizione - Ci permette di avvisarti quando un\'allerta viene diramata.
/allertameteo disiscrizione - Ti permette di non ricevere più avvisi riguardanti eventuali allerte meteo.
/allertameteo info - Ti fa leggere di nuovo questo elenco.';

$lat = 0;
$lon = 0;

date_default_timezone_set('UTC+2');


//---- STAMPA LISTA COMANDI
if($domandaL == 'aiuto' or $domandaL == '/aiuto' or $domandaL == 'aiutami' or $domandaL == 'help' or $domandaL == '/help')
	$risposta = trim($listacomandi);

//---- STAMPA ORARIO
if($domandaL == 'che ore sono?' or $domandaL == 'mi dici l\'orario?' or $domandaL == 'sai dirmi l\'orario?' or $domandaL == 'ore?' or $domandaL == 'mi dici l\'ora?' or $domandaL == 'sai dirmi l\'ora?')
	$risposta = trim(date("H:i:s"));

//---- STAMPA CONTATTI
if($domandaL == 'contatti' or $domandaL == '/contatti')
	$risposta = trim($contattiAss);

//---- KEY ISCRIZIONE
if(substr($domandaL,0,10) == 'iscrizione' or substr($domandaL,0,11) == '/iscrizione')
{
	if(substr($domandaL,0,1) == '/')
		$codsocio = substr($domandaL,12);
	else
		$codsocio = substr($domandaL,11);
	
	$codsocio = trim($codsocio);
	$urlUser = 'http://www.protezionecivilecasarano.org/gestionale/api/read_user.php?codsocio='.$codsocio;
	$json = file_get_contents($urlUser);
	$obj = json_decode($json,true);
	$array = $obj[1];
	$array2 = $array["name_array"];
	$data = $array2[0];
	$nome = $data["nome"];
	$cognome = $data["cognome"];
	
	if($nome == '' or $cognome == '')
		$risposta = trim('Mi dispiace non ti ho riconosciuto. Ho letto bene il tuo codice socio? Mi risulta >>'.$codsocio.'<<');
	else
	{
		$risposta = trim('Ciao, ti ho riconosciuto, sei proprio '.$nome.' '.$cognome.'! D\'ora in poi saprò come chiamarti quando mi servirai.
		
		Se lo volessi comunicare direttamente in segreteria il tuo codice telegram è '.$chatId);
		$urlUserAppr = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user.php?codsocio='.$codsocio.'&chatid='.$chatId;
		$json = file_get_contents($urlUserAppr);
	}
	
	if($codsocio == '')
		$risposta = trim('Non posso riconoscerti se non mi fornisci il tuo codice tessera');
}

//---- KEY PREVISIONI
if(substr($domandaL,0,10) == 'previsioni' or substr($domandaL,0,11) == '/previsioni' or
	substr($domandaL,0,10) == 'previsione' or substr($domandaL,0,11) == '/previsione')
{
	
	if(substr($domandaL,0,1) == '/')
		$dataprev = conv_date(substr(str_replace("/","-",$domandaL),12,22));
	else
		$dataprev = conv_date(substr(str_replace("/","-",$domandaL),11,21));
	
	if($dataprev!='')
	{
		//$dataprev = trim($dataprev);
		$urlPrev = 'http://www.protezionecivilecasarano.org/stazionemeteo/apiApp/read_prev_test.php?par='.$dataprev;
		$json = file_get_contents($urlPrev);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$c = $obj[0]["conteggio"];
		$data = $array["name_array"];
		$data = $data[0];
		$tmax = $data["tmax"];
		$tmin = $data["tmin"];
		$percUmid = $data["percumid"];
		$giornoT = $data["giornoTitolo"];
		
		if($c >= 1)
		{
			$risposta = "Per ".conv_date($dataprev)." prevediamo una temperatura massima di ".$tmax." °C e una temperatura minima di ".$tmin." °C con una percentuale di umidità pari al ".$percumid."%. 
			";
				
			$urlPrev = 'http://www.protezionecivilecasarano.org/stazionemeteo/apiApp/read_prev_giorno.php?par='.$dataprev;
			$json = file_get_contents($urlPrev);
			$obj = json_decode($json,true);
			$array = $obj[1];
			$data = $array["name_array"];
			
			
			for($i=0;$i<4;$i++)
			{
				$orario = $data[$i]["orario"];
				$fenomeno = $data[$i]["fenomeno"];
				$sitcielo = $data[$i]["sitcielo_descr"];
				$percfen = $data[$i]["percfen"];
				$intvento = $data[$i]["intvento"];
				$dirvento = $data[$i]["dirvento"];
				
				$risposta .= "
				Nella fascia oraria ".$orario." prevediamo cielo ".$sitcielo." con una possibilità del ".$percfen."% di ".$fenomeno.". Avremo un vento da ".$dirvento." con un'intensità di ".$intvento."km/h. ";
			}
		

		}
		else
		{
			$risposta = trim('Hai chiesto una previsione meteo ma non ho capito bene di che giorno. Sicuro di aver scritto bene la data nel formato gg/mm/aaaa? Se hai scritto la data in modo corretto potresti star chiedendo una previsione troppo lontana o non ancora formulata.
			
		
		Esempio:
		/previsioni '.date("d/m/Y"));
		}
	}
	else
		{
			$risposta = trim('Hai chiesto una previsione meteo ma non ho capito bene di che giorno. Sicuro di aver scritto bene la data nel formato gg/mm/aaaa? Se hai scritto la data in modo corretto potresti star chiedendo una previsione troppo lontana o non ancora formulata.
			
		
		Esempio:
		/previsioni '.date("d/m/Y"));
		}

}

//---- KEY GESTIONE SERVIZIO - SI
if(substr($domandaL,0,2) == 'si' or substr($domandaL,0,3) == '/si')
{
	if(substr($domandaL,0,1) == '/')
		$codserv = substr($domandaL,3);
	else
		$codserv = substr($domandaL,2);
	
	if(se_iscritto($chatId,'NOME')!='NO')
	{
		$codserv = trim($codserv);
		$urlUser = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user_servizio.php?serv='.$codserv.'&chatid='.$chatId.'&risp=SI';
		$json = file_get_contents($urlUser);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$risultato = $data["result"];
		
		if(substr($risultato,0,6) != 'ERRORE')
			$risposta = trim('Grazie per la tua disponibilità '.se_iscritto($chatId,'NOME').' ho segnato la tua presenza, mi raccomando non te ne dimenticare.');
		else
			$risposta = trim(substr($risultato,7));
	}
	else	
		$risposta = trim('Funzione di accettazione o rifiuto servizi disponibile sono ai registrati. Vuoi iscriverti in Protezione Civile? Vienici a trovare o Scrivici. Tutti i contatti sono a tua disposizione scrivendo /contatti');
}

//---- KEY GESTIONE SERVIZIO - NO
if(substr($domandaL,0,2) == 'no' or substr($domandaL,0,3) == '/no')
{
	if(substr($domandaL,0,1) == '/')
		$codserv = substr($domandaL,2);
	else
		$codserv = substr($domandaL,3);
	
	if(se_iscritto($chatId,'NOME')!='NO')
	{
		$codserv = trim($codserv);
		$urlUser = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user_servizio.php?serv='.$codserv.'&chatid='.$chatId.'&risp=NO';
		$json = file_get_contents($urlUser);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$risultato = $data["result"];
		
		if(substr($risultato,0,6) != 'ERRORE')
			$risposta = trim('Nooooo '.se_iscritto($chatId,'NOME').' mi dispiace che tu non possa essere dei nostri. Sarà per una prossima volta allora.');
		else
			$risposta = trim(substr($risultato,7));
	}
	else	
		$risposta = trim('Funzione di accettazione o rifiuto servizi disponibile sono ai registrati. Vuoi iscriverti in Protezione Civile? Vienici a trovare o Scrivici. Tutti i contatti sono a tua disposizione scrivendo /contatti');
}

//---- KEY LUOGO
if(substr($domandaL,0,5) == 'luogo' or substr($domandaL,0,6) == '/luogo')
{
	if(substr($domandaL,0,1) == '/')
		$codserv = substr($domandaL,6);
	else
		$codserv = substr($domandaL,5);
	
	if(se_iscritto($chatId,'NOME')!='NO')
	{
		$codserv = trim($codserv);
		$url = 'http://www.protezionecivilecasarano.org/gestionale/api/read_luogo_serv.php?serv='.$codserv;
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$data = $array["name_array"];
		//$data = $array2[0];

		
		
		if(substr($data["mex"],0,6) != 'ERRORE')
		{
			$lat = $data["lat"];
			$lon = $data["lon"];
			$parameters = array('chat_id' => $chatId, "latitude" => $lat, "longitude" => $lon);
			$parameters["method"] = "sendLocation";
			echo json_encode($parameters);
			exit;
		}
		else
			$risposta = trim(substr($data["mex"],7));
	}
	else	
		$risposta = trim('Funzione di coordinate del luogo del servizio disponibile sono ai registrati. Vuoi iscriverti in Protezione Civile? Vienici a trovare o Scrivici. Tutti i contatti sono a tua disposizione scrivendo /contatti');
	
}	

//---- KEY OPERATORI
if(substr($domandaL,0,9) == 'operatori' or substr($domandaL,0,10) == '/operatori')
{
	if(substr($domandaL,0,1) == '/')
		$codserv = substr($domandaL,10);
	else
		$codserv = substr($domandaL,9);
	
	if(se_iscritto($chatId,'NOME')!='NO')
	{
		$codserv = trim($codserv);
		$url = 'http://www.protezionecivilecasarano.org/gestionale/api/read_op_serv.php?serv='.$codserv;
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$operatori = $data["operatori"];
		
		
		if(substr($risultato,0,6) != 'ERRORE')
			$risposta = trim('Allora caro '.se_iscritto($chatId,'NOME').' gli operatori iscritti al servizio sono '.$operatori);
		else
			$risposta = trim(substr($risultato,7));
	}
	else	
		$risposta = trim('Conoscere gli operatori in un determinato servizio è disponibile sono ai registrati. Vuoi iscriverti in Protezione Civile? Vienici a trovare o Scrivici. Tutti i contatti sono a tua disposizione scrivendo /contatti');
	
}

//---- KEY ALLERTEMETEO
if(substr($domandaL,0,13) == '/allertameteo')
{
	$codop = strtolower(substr($domandaL,14));
	
	if($codop == '' or $codop == 'info')
		$risposta = $istr_allerameteo;
	if($codop == 'ultima')
	{
		$url = 'http://www.protezionecivilecasarano.org/gestionale/api/read_allerte_cvpc.php?par1=1';
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$titolo = $data["titolo"];
		$descrizione = $data["descrizione"];
		
		$risposta = $titolo.'
		
		'.$descrizione;
	}
	if($codop == 'iscrizione')
	{
		$url = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user_allerte.php?chatid='.$chatId.'&nome='.$firstname.'&cognome='.$lastname.'&act=I';
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$risultato = $data["result"];
		
		if(substr($risultato,0,6) != 'ERRORE')
			$risposta = trim('Bene '.$firstname.' ti ho iscritto nell\'elenco di persone da avvisare per la pubblicazione delle nuove allerte meteo. Per cancellare la tua iscrizione scrivimi /allertameteo disiscrizione');
		else
			$risposta = trim(substr($risultato,7));
	}
	if($codop == 'disiscrizione')
	{
		$url = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user_allerte.php?chatid='.$chatId.'&nome='.$firstname.'&cognome='.$lastname.'&act=D';
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$risultato = $data["result"];
		
		if(substr($risultato,0,6) != 'ERRORE')
			$risposta = trim('Bene '.$firstname.' ti ho rimosso nell\'elenco di persone da avvisare per la pubblicazione delle nuove allerte meteo. Se ci ripensi scrivimi /allertameteo iscrizione');
		else
			$risposta = trim(substr($risultato,7));
	}
	
}

//---- KEY SCRIVI
if(substr($domandaL,0,7) == '/scrivi')
{
	$messaggioTele = substr($domandaL,8);
	
	if($messaggioTele == '')
		$risposta = "Scrivo si, ma cosa? devi dirmi cosa mandare quindi, scivimi questo comando, così: /scrivi tutto il testo che vuoi mandare";
	else
	{
		/*$url = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_user_allerte.php?chatid='.$chatId.'&nome='.$firstname.'&cognome='.$lastname.'&act=D';
		$json = file_get_contents($url);
		$obj = json_decode($json,true);
		$array = $obj[1];
		$array2 = $array["name_array"];
		$data = $array2[0];
		$risultato = $data["result"];*/
		
		// L'INDIRIZZO DEL DESTINATARIO DELLA MAIL
		$to = 'presidenza@protezionecivilecasarano.org'; 

		// IL SOGGETTO DELLA MAIL
		$subject = "Messaggio da telegram";

		// COSTRUIAMO IL CORPO DEL MESSAGGIO
		$body = "<p>Caro Presidente</p> <p> <b>".$firstname." ".$lastname."</b> ha voluto mandarci un messaggio tramite Telegram.\n</p>";
		$body .= "<p>Testo del Messaggio:\n</p><br>";     
		$body .= "<p>".$messaggioTele."</p>";
		$body .= "<p> </p>";
		$body .= "<p> </p>";
		$body .= "<p>Chat ID: ".$chatId."</p>";
		$body .= "<p> </p>";
		$body .= "<p>\n<br>\nIl tuo caro sistema automatico<br></p>"; 


		// INTESTAZIONI SUPPLEMENTARI
		$headers = "BOT Telegram Protezione Civile Casarano<noreply@protezionecivilecasarano.org>";

		// INVIO DELLA MAIL
		if(@Sendmail($headers, $to,$subject, $body))
			$risposta = trim('Bene '.$firstname.' ho inviato il tuo messaggio. Se hai lasciato un tuo recapito ti conatteranno per risponderti.');
		else// ALTRIMENTI...
			$risposta = "Si sono verificati dei problemi nell'invio della mail alla segreteria. Contatta la segreteria dell'associazione ed informala.";
		//-------------------------------------------------------------------------
	}
	
}

//---- KEY FRASE
if((trova_parola('dimmi',$domandaL) or trova_parola('dirmi',$domandaL)) and trova_parola('frase',$domandaL))
{
	$urlUser = 'http://www.protezionecivilecasarano.org/gestionale/api/read_frase.php';
	$json = file_get_contents($urlUser);
	$obj = json_decode($json,true);
	$frase = $obj["result"];
		
	$parameters = array('chat_id' => $chatId, "text" => $frase, "parse_mode" => "Markdown");
	$parameters["method"] = "sendMessage";
	echo json_encode($parameters);
	exit;
	
}

//---- KEY ISCRIVIAVVISI
if($domandaL == '/iscriviavvisi' )
{
	
		$risposta = trim('Perfetto! Grazie a quanto hai appena fatto sarai sempre informato di quanto sta succedendo nella tua città. Puoi richiederne la cancellazione con il comando /cancellaavvisi. Ti voglio inoltre ricordare che puoi contattarci direttamente per qualsiasi cosa, come? scrivi /contatti e avrai tutto più chiaro.');
		$urlUserAppr = 'http://www.protezionecivilecasarano.org/gestionale/api/registra_utente_avvisi.php?chatid='.$chatId;
		$json = file_get_contents($urlUserAppr);
	

}

//---- KEY CANCELLAAVVISI
if($domandaL == '/cancellaavvisi' )
{
	
		$risposta = trim('Mi dispiace per la tua scelta ma non ti preoccupare da ora in poi non verrai più avvisato di nulla. Ti voglio inoltre ricordare che puoi contattarci direttamente per qualsiasi cosa, come? scrivi /contatti e avrai tutto più chiaro.');
		$urlUserAppr = 'http://www.protezionecivilecasarano.org/gestionale/api/cancella_utente_avvisi.php?chatid='.$chatId;
		$json = file_get_contents($urlUserAppr);
	

}



//---- KEY CIAO
if($domandaL=='ciao' or $domandaL=='salve' 
or $domandaL=='buongiorno' or $domandaL=='buon giorno'
or $domandaL=='buonasera' or $domandaL=='buona sera'
or $domandaL=='buonpomeriggio' or $domandaL=='buon pomeriggio')
{
	$risposta = "Ciao! Come posso esserti utile?";	
}


$parameters = array('chat_id' => $chatId, "text" => $risposta);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
