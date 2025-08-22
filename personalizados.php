<?php
// formulario_personalizado.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diseña tu pastel</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: linear-gradient(135deg, #fef3e0, #f8d58c); min-height:100vh; display:flex; flex-direction:column; align-items:center; padding:20px; }
.eslogan { margin-top:20px; margin-bottom:10px; text-align:center; font-size:1.2rem; color:#5c3a00; font-style:italic; }
form { background:#fff; padding:25px 35px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.15); width:100%; max-width:550px; }
label { display:block; margin-top:15px; margin-bottom:5px; font-weight:bold; color:#333; }
input[type="text"], input[type="email"], input[type="date"], input[type="file"], textarea, select { width:100%; padding:12px 15px; border-radius:8px; border:1px solid #ccc; font-size:14px; transition:all 0.3s ease; }
input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus, textarea:focus, input[type="file"]:focus, select:focus { border-color:#f8b500; outline:none; box-shadow:0 0 5px rgba(248,181,0,0.4); }
textarea { resize:vertical; min-height:80px; }
.checkbox-group { display:flex; flex-wrap:wrap; gap:10px; }
.checkbox-group label { font-weight:normal; font-size:0.9rem; }
button { margin-top:20px; width:100%; padding:15px; background-color:#f8b500; border:none; border-radius:8px; color:#fff; font-size:16px; font-weight:bold; cursor:pointer; transition:background 0.3s ease, transform 0.2s ease; }
button:hover { background-color:#e09e00; transform:translateY(-2px); }
#previewImage { max-width:100%; display:none; border:1px solid #ccc; border-radius:8px; margin-top:10px; text-align:center; }
.back-button { margin-bottom:15px; background-color:#6c757d; width:100%; max-width:550px; }
.back-button:hover { background-color:#5a6268; }
@media (max-width:600px){ form{padding:20px; } }
</style>
</head>
<body>
<div class="eslogan">Diseña tu pastel y nosotros lo haremos realidad</div>

<!-- Botón de regresar -->
<button class="back-button" onclick="window.history.back();">← Regresar</button>

<form action="guardar_pastel.php" method="POST" enctype="multipart/form-data" id="pastelForm">
    <label>Nombre:</label>
    <input type="text" name="fullName" id="fullName" required>

    <label>Teléfono:</label>
    <input type="text" name="phone" id="phone" required>

    <label>Email:</label>
    <input type="email" name="email" id="email" required>

    <label>Fecha de entrega:</label>
    <input type="date" name="deliveryDate" id="deliveryDate" required>

    <label>Sabores:</label>
    <div class="checkbox-group">
        <label><input type="checkbox" name="flavor[]" value="Chocolate"> Chocolate</label>
        <label><input type="checkbox" name="flavor[]" value="Vainilla"> Vainilla</label>
        <label><input type="checkbox" name="flavor[]" value="Fresa"> Fresa</label>
        <label><input type="checkbox" name="flavor[]" value="Red Velvet"> Red Velvet</label>
        <label><input type="checkbox" name="flavor[]" value="Moca"> Moca</label>
        <label><input type="checkbox" name="flavor[]" value="Dulce de Leche"> Dulce de Leche</label>
        <label><input type="checkbox" name="flavor[]" value="Maracuyá"> Maracuyá</label>
        <label><input type="checkbox" name="flavor[]" value="Oreo"> Oreo</label>
    </div>

    <label>Porciones:</label>
    <select name="size" id="size" required>
        <option value="4">4 porciones - $5</option>
        <option value="8">8 porciones - $10</option>
        <option value="12">12 porciones - $15</option>
        <option value="16">16 porciones - $20</option>
        <option value="20">20 porciones - $25</option>
    </select>

    <label>Diseño:</label>
    <textarea name="design" id="design" required></textarea>

    <label>Foto referencia:</label>
    <input type="file" name="referencePhoto" id="referencePhoto" accept="image/*">
    <div style="text-align:center;"><img id="previewImage" src="" alt="Vista previa"></div>

    <button type="submit">Enviar pedido</button>
</form>

<script>
// Vista previa de la foto
document.getElementById('referencePhoto').addEventListener('change', function(){
    const file = this.files[0];
    const preview = document.getElementById('previewImage');
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){ preview.src=e.target.result; preview.style.display='block'; }
        reader.readAsDataURL(file);
    } else { preview.style.display='none'; }
});

// Fecha mínima 2 días después
document.addEventListener("DOMContentLoaded", function(){
    const today = new Date();
    const minDate = new Date(today); minDate.setDate(today.getDate()+2);
    const dd = String(minDate.getDate()).padStart(2,'0');
    const mm = String(minDate.getMonth()+1).padStart(2,'0');
    const yyyy = minDate.getFullYear();
    document.getElementById('deliveryDate').setAttribute('min', `${yyyy}-${mm}-${dd}`);
});

// Resetear formulario después de enviar
document.getElementById('pastelForm').addEventListener('submit', function() {
    // Esto se ejecutará después de que el formulario se haya enviado
    setTimeout(() => {
        this.reset(); // Limpia todos los campos del formulario
        document.getElementById('previewImage').style.display = 'none'; // Oculta la vista previa
    }, 0);
});
</script>
</body>
</html>











