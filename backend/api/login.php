<?php require_once __DIR__.'/../config/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST')jsonResponse(['success'=>false,'message'=>'Method không được hỗ trợ.'],405);
$d=inputJson();$login=trim((string)($d['login']??$d['username']??''));$pw=(string)($d['password']??'');
if($login===''||$pw==='')jsonResponse(['success'=>false,'message'=>'Vui lòng nhập tài khoản/email và mật khẩu.'],422);
$s=db()->prepare('SELECT id,username,email,full_name,password_hash,role,status FROM accounts WHERE username=? OR email=? LIMIT 1');$s->execute([$login,$login]);$a=$s->fetch();
if(!$a||$a['status']!=='active'||!password_verify($pw,$a['password_hash']))jsonResponse(['success'=>false,'message'=>'Sai tài khoản/email hoặc mật khẩu.'],401);
$token=bin2hex(random_bytes(32));$s=db()->prepare('INSERT INTO account_tokens(account_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 12 HOUR))');$s->execute([(int)$a['id'],hash('sha256',$token)]);
jsonResponse(['success'=>true,'token'=>$token,'account'=>['id'=>(int)$a['id'],'username'=>$a['username'],'email'=>$a['email'],'full_name'=>$a['full_name'],'role'=>$a['role']]]);
