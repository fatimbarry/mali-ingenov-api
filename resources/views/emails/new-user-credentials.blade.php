<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Bienvenue chez Mali-Ingenov</h1>
    </div>

    <div class="content">
        <p>Bonjour {{ $user->prenom }} {{ $user->nom }},</p>

        <p>Votre compte a été créé avec succès dans notre système de gestion de projet. Voici vos identifiants de connexion :</p>

        <div class="credentials">
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe temporaire :</strong> {{ $password }}</p>
        </div>

        <p>Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe dès votre première connexion en accédant à votre profil.</p>

        <p>Pour vous connecter, veuillez visiter notre plateforme et utiliser les identifiants ci-dessus.</p>
    </div>

    <div class="footer">
        <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} Mali-Ingenov. Tous droits réservés.</p>
    </div>
</div>
</body>
</html>
