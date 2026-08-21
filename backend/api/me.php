<?php require_once __DIR__.'/../config/bootstrap.php'; $a=requireLogin(); jsonResponse(['success'=>true,'account'=>$a]);
