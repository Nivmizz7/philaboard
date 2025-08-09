<?php
$db_file = __DIR__ . '/db.json';
$upload_dir = __DIR__ . '/uploads';
if (!file_exists($db_file)) file_put_contents($db_file, json_encode([]));
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

$data = json_decode(file_get_contents($db_file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . '/' . $fileName);
        } else {
            $fileName = $_POST['currentImage'] ?? '';
        }
        if ($_POST['action'] === 'add') {
            $data[] = [
                'id' => uniqid(),
                'nom' => $_POST['nom'],
                'annee' => $_POST['annee'],
                'nyt' => $_POST['nyt'],
                'album' => $_POST['album'],
                'pays' => $_POST['pays'],
                'categorie' => $_POST['categorie'],
                'etat' => $_POST['etat'],
                'quantite' => $_POST['quantite'],
                'image' => $fileName
            ];
        } else {
            foreach ($data as &$stamp) {
                if ($stamp['id'] === $_POST['id']) {
                    $stamp['nom'] = $_POST['nom'];
                    $stamp['annee'] = $_POST['annee'];
                    $stamp['nyt'] = $_POST['nyt'];
                    $stamp['album'] = $_POST['album'];
                    $stamp['pays'] = $_POST['pays'];
                    $stamp['categorie'] = $_POST['categorie'];
                    $stamp['etat'] = $_POST['etat'];
                    $stamp['quantite'] = $_POST['quantite'];
                    $stamp['image'] = $fileName;
                }
            }
        }
    }
    if ($_POST['action'] === 'delete') {
        $data = array_values(array_filter($data, fn($s) => $s['id'] !== $_POST['id']));
    }
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Collection de timbres</title>
<style>
body { font-family: Verdana, sans-serif; font-size: 14px; background: #f9f9f9; margin: 20px; }
table { border-collapse: collapse; width: 100%; background: #fff; }
th, td { border: 1px solid #999; padding: 5px; text-align: center; }
th { background: #ddd; }
td img { max-width: 80px; max-height: 80px; cursor: pointer; border: 1px solid #ccc; }
input[type=text], input[type=number], select {
  font-size: 13px; padding: 4px; width: 100%;
  box-sizing: border-box; border: 1px solid #999; border-radius: 0;
  background: #fff;
}
button {
  font-size: 13px; padding: 5px 10px; margin: 2px 5px 2px 0;
  border: 1px solid #555; background: #eee; cursor: pointer;
  border-radius: 0; /* carré */
  transition: background 0.2s;
}
button:hover { background: #ddd; }
#addButton {
  margin-bottom: 10px;
}
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); text-align:center; }
.modal-content {
  background:#fff; display:inline-block; margin-top:50px; padding:15px 25px;
  border:1px solid #666; max-width:400px; text-align:left;
  box-sizing: border-box;
}
.footer { text-align:center; margin-top:20px; font-size:11px; }
.footer img { border:0; }
</style>
</head>
<body>
<a href="http://localhost" 
   style="display:inline-block; padding:5px 10px; background:#ece9d8; border:2px solid #808080; text-decoration:none; color:black; font-family:Tahoma, sans-serif; font-size:14px;">
    &#8592; Retour
</a>
<h3>COLLEC</h3>
<button id="addButton" onclick="openForm()">Ajouter un timbre</button>
<table>
<thead>
<tr>
<th>Image</th>
<th>Nom</th>
<th>Année</th>
<th>YT</th>
<th>Album</th>
<th>Pays</th>
<th>Catégorie</th>
<th>État</th>
<th>Quantité</th>
<th>Actions</th>
</tr>
<tr>
<th></th>
<th><input type="text" id="searchNom" onkeyup="filterTable(1)" placeholder="Chercher par nom"></th>
<th><input type="text" id="searchAnnee" onkeyup="filterTable(2)" placeholder="Chercher par année"></th>
<th><input type="text" id="searchYT" onkeyup="filterTable(3)" placeholder="Chercher par YT"></th>
<th><input type="text" id="searchAlbum" onkeyup="filterTable(4)" placeholder="Chercher par album"></th>
<th><input type="text" id="searchPays" onkeyup="filterTable(5)" placeholder="Chercher par pays"></th>
<th><input type="text" id="searchCategorie" onkeyup="filterTable(6)" placeholder="Chercher par catégorie"></th>
<th><input type="text" id="searchEtat" onkeyup="filterTable(7)" placeholder="Chercher par état"></th>
<th><input type="text" id="searchQuantite" onkeyup="filterTable(8)" placeholder="Chercher par quantité"></th>
<th></th>
</tr>
</thead>
<tbody id="stampTable">
<?php foreach($data as $s): ?>
<tr>
<td><?php if($s['image']) echo "<img src='uploads/{$s['image']}' onclick=\"viewStamp('{$s['id']}')\" alt='Image timbre'>"; ?></td>
<td><?= htmlspecialchars($s['nom']) ?></td>
<td><?= htmlspecialchars($s['annee']) ?></td>
<td><?= htmlspecialchars($s['nyt']) ?></td>
<td><?= htmlspecialchars($s['album']) ?></td>
<td><?= htmlspecialchars($s['pays']) ?></td>
<td><?= htmlspecialchars($s['categorie']) ?></td>
<td><?= htmlspecialchars($s['etat']) ?></td>
<td><?= htmlspecialchars($s['quantite']) ?></td>
<td>
<button onclick="editStamp('<?= $s['id'] ?>')">Modifier</button>
<button onclick="confirmDelete('<?= $s['id'] ?>')">Supprimer</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Formulaire modal -->
<div id="formModal" class="modal">
<div class="modal-content">
<h4 id="formTitle">Ajouter un timbre</h4>
<form method="POST" enctype="multipart/form-data" id="stampForm">
<input type="hidden" name="id" id="stampId">
<input type="hidden" name="currentImage" id="currentImage">
<label>Nom:<br><input type="text" name="nom" id="nom" required></label><br><br>
<label>Année:<br><input type="number" name="annee" id="annee"></label><br><br>
<label>YT:<br><input type="text" name="nyt" id="nyt"></label><br><br>
<label>Album:<br><input type="text" name="album" id="album"></label><br><br>
<label>Pays:<br><input type="text" name="pays" id="pays"></label><br><br>
<label>Catégorie:<br>
<select name="categorie" id="categorie">
<option>Timbre Poste</option>
<option>Poste aérienne</option>
<option>Timbre Taxe</option>
<option>Timbre Préoblitéré</option>
<option>Timbre de Service</option>
<option>Timbre de Franchise Militaire</option>
<option>Autre</option>
</select>
</label><br><br>
<label>État:<br>
<select name="etat" id="etat">
<option>neuf</option>
<option>charniere</option>
<option>neuf_sans_gomme</option>
<option>oblitere</option>
</select>
</label><br><br>
<label>Quantité:<br><input type="number" name="quantite" id="quantite" min="1" value="1"></label><br><br>
<label>Image:<br><input type="file" name="image" id="image" accept="image/*"></label><br><br>
<input type="hidden" name="action" id="formAction" value="add">
<button type="submit">Enregistrer</button>
<button type="button" onclick="closeForm()">Annuler</button>
</form>
</div>
</div>

<!-- Visualisation modal -->
<div id="viewModal" class="modal">
<div class="modal-content" id="viewContent"></div>
</div>

<!-- Suppression modal -->
<div id="deleteModal" class="modal">
<div class="modal-content">
<p>Supprimer ce timbre ?</p>
<form method="POST">
<input type="hidden" name="id" id="deleteId">
<input type="hidden" name="action" value="delete">
<button type="submit">Oui</button>
<button type="button" onclick="closeDelete()">Non</button>
</form>
</div>
</div>

<div class="footer">
<a href="https://validator.w3.org/check?uri=referer" target="_blank">
<img src="https://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01"></a>
<a href="https://jigsaw.w3.org/css-validator/check/referer" target="_blank">
<img src="https://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!"></a>
</div>

<script>
function openForm(){
  document.getElementById('formTitle').textContent = 'Ajouter un timbre';
  document.getElementById('formAction').value = 'add';
  document.getElementById('stampForm').reset();
  document.getElementById('stampId').value = '';
  document.getElementById('currentImage').value = '';
  document.getElementById('formModal').style.display = 'block';
}
function closeForm(){ document.getElementById('formModal').style.display = 'none'; }
function viewStamp(id){
  let stamps = <?php echo json_encode($data); ?>;
  let s = stamps.find(x => x.id === id);
  let html = "<h3>"+s.nom+"</h3>";
  if(s.image) html += "<img src='uploads/"+s.image+"' style='max-width:100%;max-height:300px;display:block;margin:10px auto;'><br>";
  html += "<b>Année:</b> "+s.annee+"<br>";
  html += "<b>YT:</b> "+s.nyt+"<br>";
  html += "<b>Album:</b> "+s.album+"<br>";
  html += "<b>Pays:</b> "+s.pays+"<br>";
  html += "<b>Catégorie:</b> "+s.categorie+"<br>";
  html += "<b>État:</b> "+s.etat+"<br>";
  html += "<b>Quantité:</b> "+s.quantite+"<br>";
  document.getElementById('viewContent').innerHTML = html;
  document.getElementById('viewModal').style.display = 'block';
}
function closeView(){ document.getElementById('viewModal').style.display = 'none'; }

function editStamp(id){
  let stamps = <?php echo json_encode($data); ?>;
  let s = stamps.find(x => x.id === id);
  document.getElementById('formTitle').textContent = 'Modifier un timbre';
  document.getElementById('formAction').value = 'update';
  document.getElementById('stampId').value = s.id;
  document.getElementById('nom').value = s.nom;
  document.getElementById('annee').value = s.annee;
  document.getElementById('nyt').value = s.nyt;
  document.getElementById('album').value = s.album;
  document.getElementById('pays').value = s.pays;
  document.getElementById('categorie').value = s.categorie;
  document.getElementById('etat').value = s.etat;
  document.getElementById('quantite').value = s.quantite;
  document.getElementById('currentImage').value = s.image;
  document.getElementById('formModal').style.display = 'block';
}
function confirmDelete(id){
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteModal').style.display = 'block';
}
function closeDelete(){ document.getElementById('deleteModal').style.display = 'none'; }

// Filter Table by column index, simple case-insensitive contains
function filterTable(colIndex){
  let inputIdMap = ['','searchNom','searchAnnee','searchYT','searchAlbum','searchPays','searchCategorie','searchEtat','searchQuantite'];
  let input = document.getElementById(inputIdMap[colIndex]);
  let filter = input.value.toLowerCase();
  let table = document.getElementById("stampTable");
  let tr = table.getElementsByTagName("tr");
  for(let i=0; i<tr.length; i++){
    let td = tr[i].getElementsByTagName("td")[colIndex];
    if(td){
      let txt = td.textContent || td.innerText;
      tr[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
  }
}
window.onclick = function(event) {
  if(event.target === document.getElementById('formModal')) closeForm();
  if(event.target === document.getElementById('viewModal')) closeView();
  if(event.target === document.getElementById('deleteModal')) closeDelete();
};
</script>

</body>
</html>
