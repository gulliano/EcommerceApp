<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Shop - Shopping Destination</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Styles personnalisés pour l'image de fond et le dégradé du Hero */
        .hero-bg {
            background-image: url('placeholder-image-plant.png'); /* Remplacer par l'URL de l'image */
            background-size: cover;
            background-position: center;
        }
        .category-img {
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 9999px; /* Cercle */
        }
        /* Dégradé personnalisé pour la barre supérieure (simulé) */
        .top-bar-gradient {
            background-color: #f7e6a7; /* Couleur jaune-crème */
        }
    </style>
</head>
<body class="font-sans bg-gray-100">
    <x-navheader />

    @yield('content')

</body>
</html>