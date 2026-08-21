<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
$origin=getenv('FRONTEND_ORIGIN')?:'*'; header("Access-Control-Allow-Origin: {$origin}"); header('Vary: Origin');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;} require_once __DIR__.'/db.php';
function jsonResponse(array $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function inputJson():array{$d=json_decode(file_get_contents('php://input'),true);return is_array($d)?$d:[];}
function bearerToken():?string{$h=$_SERVER['HTTP_AUTHORIZATION']??'';return preg_match('/Bearer\s+(.+)/i',$h,$m)?trim($m[1]):null;}
function currentAccount():?array{$t=bearerToken();if(!$t)return null;$s=db()->prepare('SELECT a.id,a.username,a.email,a.full_name,a.role,a.status FROM account_tokens t JOIN accounts a ON a.id=t.account_id WHERE t.token_hash=? AND t.expires_at>NOW() LIMIT 1');$s->execute([hash('sha256',$t)]);$a=$s->fetch();if(!$a||$a['status']!=='active')return null;$a['id']=(int)$a['id'];return $a;}
function requireLogin():array{$a=currentAccount();if(!$a)jsonResponse(['success'=>false,'message'=>'Bạn chưa đăng nhập hoặc phiên đã hết hạn.'],401);return $a;}
function requireAdmin():array{$a=requireLogin();if($a['role']!=='admin')jsonResponse(['success'=>false,'message'=>'Bạn không có quyền quản trị.'],403);return $a;}
