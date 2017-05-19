<?php

	header("Content-Type: text/html; charset=utf-8");

	error_reporting(0);

	require_once("functions.php");


	$mail_login    = "php@rekhod.ru";
	$mail_password = "phpphp";
	$mail_imap 	   = "{imap.mail.ru:993/imap/ssl}";

	// Список учитываемых типов файлов
	$mail_filetypes = array(
		"MSWORD"
	);

	$connection = imap_open($mail_imap, $mail_login, $mail_password);

	if(!$connection){

		echo("Ошибка соединения с почтой - ".$mail_login);
		exit;
	}else{
	
		echo $msg_num = imap_num_msg($connection);

		$mails_data = array();

		for($i = 1; $i >= 10, $i <= 30; $i++){
		//for($i = 1; $i <= $msg_num; $i++){

			// Шапка письма
			$msg_header = imap_header($connection, $i);

			$mails_data[$i]["time"] = time($msg_header->MailDate);
			$mails_data[$i]["date"] = $msg_header->MailDate;

			foreach($msg_header->to as $data){

				$mails_data[$i]["to"] = $data->mailbox."@".$data->host;
			}

			foreach($msg_header->from as $data){

				$mails_data[$i]["from"] = $data->mailbox."@".$data->host;
			}

			$mails_data[$i]["title"] = get_imap_title($msg_header->subject);

			// Тело письма
			$msg_structure = imap_fetchstructure($connection, $i);
			$msg_body 	   = imap_fetchbody($connection, $i, 1);
			$body 		   = "";

			$recursive_data = recursive_search($msg_structure);

			if($recursive_data["encoding"] == 0 ||
			   $recursive_data["encoding"] == 1){

				$body = $msg_body;
			}

			if($recursive_data["encoding"] == 4){

				$body = structure_encoding($recursive_data["encoding"], $msg_body);
			}

			if($recursive_data["encoding"] == 3){

				$body = structure_encoding($recursive_data["encoding"], $msg_body);
			}

			if($recursive_data["encoding"] == 2){

				$body = structure_encoding($recursive_data["encoding"], $msg_body);
			}

			if(!check_utf8($recursive_data["charset"])){

				$body = convert_to_utf8($recursive_data["charset"], $msg_body);
			}


			$mails_data[$i]["body"] = $body;
			//$mails_data[$i]["body"] = base64_encode($body);
			//echo base64_decode($str);

			// Вложенные файлы
			// if(isset($msg_structure->parts)){

			// 	for($j = 1, $f = 2; $j < count($msg_structure->parts); $j++, $f++){

			// 		if(in_array($msg_structure->parts[$j]->subtype, $mail_filetypes)){

			// 			$mails_data[$i]["attachs"][$j]["type"] = $msg_structure->parts[$j]->subtype;
			// 			$mails_data[$i]["attachs"][$j]["size"] = $msg_structure->parts[$j]->bytes;
			// 			$mails_data[$i]["attachs"][$j]["name"] = get_imap_title($msg_structure->parts[$j]->parameters[0]->value);
			// 			$mails_data[$i]["attachs"][$j]["file"] = structure_encoding(
			// 				$msg_structure->parts[$j]->encoding,
			// 				imap_fetchbody($connection, $i, $f)
			// 			);

			// 			//file_put_contents("tmp/".iconv("utf-8", "cp1251", $mails_data[$i]["attachs"][$j]["name"]), $mails_data[$i]["attachs"][$j]["file"]);
			// 			file_put_contents("tmp/". $mails_data[$i]["attachs"][$j]["name"], $mails_data[$i]["attachs"][$j]["file"]);
			// 		}
			// 	}
			// }
		}
	}

	imap_close($connection);
?>
<html>
<head>
	<meta charset="utf-8" />
	<title> Почта | <?php echo($mail_login);?></title>
	<link href="main.css" type="text/css" rel="stylesheet" />
</head>
<body>
	<div id="page">
		<h1>Яндекс Почта (Входящие) | <?php echo($mail_login);?></h1>
		<h2>Число писем: <?php echo(count($mails_data));?></h2>
		<?php if(!isset($mails_data)):?>
		<div class="empty">писем нет</div>
		<?php else:?>
		<?php foreach($mails_data as $key => $mail):?>
		<div id="mail-<?php echo($key);?>">
			<div class="time">
				<div class="title">Временная метка:</div>
				<div class="data"><?php echo($mail["time"]);?></div>
			</div>
			<div class="date">
				<div class="title">Дата:</div>
				<div class="data"><?php echo($mail["date"]);?></div>
			</div>
			<div class="to">
				<div class="title">Кому:</div>
				<div class="data"><?php echo($mail["to"]);?></div>
			</div>
			<div class="from">
				<div class="title">От:</div>
				<div class="data"><?php echo($mail["from"]);?></div>
			</div>
			<div class="name">
				<div class="title">Тема:</div>
				<div class="data"><?php echo($mail["title"]);?></div>
			</div>
			<div class="body">
				<div class="title">Письмо в base64:</div>
				<div class="data"><?php echo($mail["body"]);?></div>
			</div>
			<?php if(isset($mail["attachs"])):?>
			<div class="attachs">
				<div class="title">Вложенные файлы:</div>
				<?php foreach($mail["attachs"] as $k => $attach):?>
				<div class="attach">
					<div class="attach-type">
						Тип: <?php echo($attach["type"]);?>
					</div>
					<div class="attach-size">
						Размер (в байтах): <?php echo($attach["size"]);?>
					</div>
					<div class="attach-name">
						Имя: <?php echo($attach["name"]);?>
					</div>
					<div class="attach-file">
						Тело: <?php echo($attach["file"]);?>
					</div>
				</div>
				<?php endforeach;?>
			</div>
			<?php endif;?>
		</div>
		<?php endforeach;?>
		<?php endif;?>
	</div>
</body>
</html>