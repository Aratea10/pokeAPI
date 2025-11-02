<?php
/*
Ejercicio:
1) Formulario (POST) con input text para nombre de Pokémon
2) Consultar PokeAPI y mostrar: nombre, id, una habilidad y la imagen
3) Si no existe, mostrar mensaje adecuado
*/

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function fetch_pokemon(string $name): array
{
    $endpoint = "https://pokeapi.co/api/v2/pokemon/" . rawurlencode($name);
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($body === false) {
        return ['ok' => false, 'status' => 0, 'error' => "Error de red: $err"];
    }
    if ($code === 404) {
        return ['ok' => false, 'status' => 404, 'error' => "No existe el Pokémon"];
    }
    if ($code < 200 || $code >= 300) {
        return ['ok' => false, 'status' => $code, 'error' => "Error HTTP $code"];
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        return ['ok' => false, 'status' => 0, 'error' => "Respuesta no válida"];
    }
    return ['ok' => true, 'status' => $code, 'data' => $data];
}

$poke = null;
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['pokemon'] ?? '');
    $nombre = strtolower($nombre);
    $nombre = preg_replace('/\s+/', '-', $nombre); // soporta nombres con espacio (mr mime -> mr-mime)

    if ($nombre === '') {
        $msg = "Introduce un nombre.";
    } else {
        $res = fetch_pokemon($nombre);
        if (!$res['ok']) {
            $msg = ($res['status'] === 404) ? "El Pokémon que buscas no existe. Prueba con otro nombre." : ("No se pudo consultar la API: " . $res['error']);
        } else {
            $d = $res['data'];
            // Nombre e ID
            $poke = [
                'name' => $d['name'] ?? $nombre,
                'id'   => $d['id']   ?? null,
            ];
            // Habilidad: primera disponible si existe
            $ability = null;
            if (!empty($d['abilities']) && is_array($d['abilities'])) {
                foreach ($d['abilities'] as $ab) {
                    if (isset($ab['ability']['name'])) {
                        $ability = $ab['ability']['name'];
                        break;
                    }
                }
            }
            $poke['ability'] = $ability;

            // Sprite: preferencia front_default
            $image = $d['sprites']['front_default'] ?? null;
            // fallback dream_world/svg o official-artwork si quieres
            if (!$image && isset($d['sprites']['other']['official-artwork']['front_default'])) {
                $image = $d['sprites']['other']['official-artwork']['front_default'];
            }
            $poke['image'] = $image;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consulta Pokémon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui;
            margin: 2rem
        }

        form {
            margin-bottom: 1.25rem;
            display: flex;
            gap: .5rem;
            align-items: center
        }

        .card {
            border: 1px solid #e5e5e5;
            border-radius: .5rem;
            padding: 1rem;
            max-width: 520px
        }

        img {
            max-width: 200px;
            height: auto
        }

        .error {
            color: #b00020
        }
    </style>
</head>

<body>
    <h1>Consulta de Pokémon</h1>

    <form action="" method="POST">
        <label for="pokemon">Nombre del Pokémon:</label>
        <input type="text" id="pokemon" name="pokemon" required placeholder="pikachu, charmander..." value="<?= h($_POST['pokemon'] ?? '') ?>">
        <button type="submit">Consultar</button>
    </form>

    <?php if ($msg): ?>
        <p class="error"><?= h($msg) ?></p>
    <?php endif; ?>

    <?php if ($poke): ?>
        <div class="card">
            <h2>Información del Pokémon</h2>
            <p><strong>Nombre:</strong> <?= h(ucfirst($poke['name'])) ?></p>
            <p><strong>ID:</strong> <?= h((string)$poke['id']) ?></p>
            <p><strong>Habilidad:</strong> <?= $poke['ability'] ? h(ucfirst($poke['ability'])) : '—' ?></p>
            <?php if ($poke['image']): ?>
                <img src="<?= h($poke['image']) ?>" alt="<?= h($poke['name']) ?>">
            <?php else: ?>
                <p>Este Pokémon no tiene sprite disponible.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>

</html>