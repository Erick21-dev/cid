<?php

function load_listings() {
    if (!file_exists('listings.json')) return [];
    $data = json_decode(file_get_contents('listings.json'), true);
    if (!is_array($data)) return [];
    
    foreach ($data as &$l) {
        $l['price'] = isset($l['price']) ? (float)$l['price'] : 0;
        $l['rating'] = isset($l['rating']) ? (int)$l['rating'] : 0;
        $l['created_at'] = $l['created_at'] ?? date('c');
    }
    return $data;
}
function save_listings($listings) {
    file_put_contents('listings.json', json_encode($listings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function next_id($listings) {
    $ids = array_column($listings,'id');
    return $ids ? max($ids)+1 : 1;
}
function h($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}

$listings = load_listings();


if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['title'])){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $subject = trim($_POST['subject']);
    $price = (float)($_POST['price'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $imagePath = '';
    if(!empty($_FILES['image']['name'])){
        if(!is_dir('uploads')) mkdir('uploads');
        $fname = time().'_'.basename($_FILES['image']['name']);
        $target = 'uploads/'.$fname;
        if(move_uploaded_file($_FILES['image']['tmp_name'],$target)){
            $imagePath = $target;
        }
    }
    $new = [
        'id'=>next_id($listings),
        'title'=>$title,
        'description'=>$description,
        'subject'=>$subject,
        'price'=>$price,
        'rating'=>$rating,
        'image'=>$imagePath,
        'created_at'=>date('c')
    ];
    $listings[]=$new;
    save_listings($listings);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}


if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $listings=array_values(array_filter($listings,fn($l)=>$l['id']!=$id));
    save_listings($listings);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}


$search=strtolower(trim($_GET['q'] ?? ''));
$subjectFilter=$_GET['subject'] ?? '';
$sort=$_GET['sort'] ?? '';

$filtered=array_filter($listings,function($l) use($search,$subjectFilter){
    $ok=true;
    if($search){
        $ok = (strpos(strtolower($l['title']),$search)!==false) || (strpos(strtolower($l['description']),$search)!==false);
    }
    if($ok && $subjectFilter){
        $ok = strtolower($l['subject'])===strtolower($subjectFilter);
    }
    return $ok;
});

if($sort==='price_asc'){
    usort($filtered,function($a,$b){return ($a['price']??0)<=>($b['price']??0);});
}elseif($sort==='price_desc'){
    usort($filtered,function($a,$b){return ($b['price']??0)<=>($a['price']??0);});
}elseif($sort==='rating_desc'){
    usort($filtered,function($a,$b){return ($b['rating']??0)<=>($a['rating']??0);});
}elseif($sort==='rating_asc'){
    usort($filtered,function($a,$b){return ($a['rating']??0)<=>($b['rating']??0);});
}

$subjects=array_unique(array_map(fn($l)=>$l['subject'],$listings));
sort($subjects);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Marketplace Acadêmico</title>
<style>
body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f4f4f4;}
header{display:flex;align-items:center;gap:12px;padding:10px;background:#fff;position:sticky;top:0;z-index:1000;box-shadow:0 2px 5px rgba(0,0,0,0.1)}
header img{height:60px;}
header h1{margin:0;font-size:22px;color:#003366;}
main{display:flex;gap:20px;padding:20px;}
section{flex:3;}
aside{flex:1;background:#fff;padding:15px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.card{background:#fff;padding:15px;margin-bottom:15px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.card img{max-width:150px;display:block;margin-bottom:10px;}
.input{width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:4px;}
.btn{padding:8px 12px;background:#003366;color:#fff;border:none;border-radius:4px;cursor:pointer;}
.btn:hover{background:#0055aa;}
</style>
</head>
<body>
<header>
  
  <img src="nati.png" alt="Logo">
  <img src="logo.jpeg" alt="Logo" style="height:80px;">

  <h1>Natalie McDonald</h1>
</header>

<main>
<section>
  <form method="get" style="display:flex;gap:8px;align-items:center;margin-bottom:15px">
    <input name="q" class="input" placeholder="Pesquisar..." value="<?=h($search)?>">
    <select name="subject" class="input" style="width:150px">
      <option value="">Todos os assuntos</option>
      <?php foreach($subjects as $s): ?>
      <option value="<?=h($s)?>" <?=$s===$subjectFilter?'selected':''?>><?=h($s)?></option>
      <?php endforeach; ?>
    </select>
    <select name="sort" class="input" style="width:150px">
      <option value="">Ordenar por...</option>
      <option value="price_asc" <?=$sort==='price_asc'?'selected':''?>>Preço (menor→maior)</option>
      <option value="price_desc" <?=$sort==='price_desc'?'selected':''?>>Preço (maior→menor)</option>
      <option value="rating_desc" <?=$sort==='rating_desc'?'selected':''?>>Avaliação (maior→menor)</option>
      <option value="rating_asc" <?=$sort==='rating_asc'?'selected':''?>>Avaliação (menor→maior)</option>
    </select>
    <button class="btn">Filtrar</button>
  </form>

  <?php foreach($filtered as $l): ?>
    <div class="card">
      <?php if($l['image']): ?><img src="<?=h($l['image'])?>" alt=""><?php endif; ?>
      <h3><?=h($l['title'])?></h3>
      <p><b>Assunto:</b> <?=h($l['subject'])?></p>
      <p><?=h($l['description'])?></p>
      <p><b>Preço:</b> R$ <?=number_format((float)($l['price']??0),2,',','.')?></p>
      <p><b>Avaliação:</b> <?= (int)($l['rating']??0) ?>/5</p>
      <a href="?delete=<?=$l['id']?>" class="btn" style="background:#c00">Excluir</a>
    </div>
  <?php endforeach; ?>
</section>

<aside>
  <h2>Inserir anúncio</h2>
  <form method="post" enctype="multipart/form-data">
    <input name="title" class="input" placeholder="Título" required>
    <input name="subject" class="input" placeholder="Assunto" required>
    <textarea name="description" class="input" placeholder="Descrição" required></textarea>
    <input name="price" type="number" step="0.01" class="input" placeholder="Preço" required>
    <input name="rating" type="number" min="1" max="5" class="input" placeholder="Avaliação (1 a 5)">
    <input type="file" name="image" class="input">
    <button class="btn">Publicar</button>
  </form>
</aside>
</main>
</body>
</html>
