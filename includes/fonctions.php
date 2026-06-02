<?php
// Ce fichier contient les fonctions utilitaires globales de l'application (accès à la base, gestion des sessions, etc.).   
/**
 * Échappe une chaîne pour sortie HTML (sécurité XSS).
 * @param string $value
 * @return string
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Exécute une requête SQL et retourne la première ligne ou null.
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function cv_db_fetch_one(string $sql, array $params = []): ?array
{
    global $pdo;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();

    return $result === false ? null : $result;
}

/**
 * Exécute une requête SQL et retourne toutes les lignes.
 * @param string $sql
 * @param array $params
 * @return array
 */
function cv_db_fetch_all(string $sql, array $params = []): array
{
    global $pdo;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Exécute une requête SQL et retourne une seule valeur (colonne).
 * Retourne $default si rien n'est trouvé.
 * @param string $sql
 * @param array $params
 * @param int $default
 * @return int
 */
function cv_db_fetch_value(string $sql, array $params = [], int $default = 0): int
{
    global $pdo;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();

    return $value === false ? $default : (int)$value;
}

/**
 * Retourne le nombre de capsules accessibles par un utilisateur (propriétaire ou invité accepté).
 * @param int $userId
 * @return int
 */
function cv_capsule_count_accessible_for_user(int $userId): int
{
    return cv_db_fetch_value(
        'SELECT COUNT(DISTINCT c.Id_Capsule)
         FROM capsule c
         WHERE c.Id_Utilisateur = ? OR c.Id_Capsule IN (
             SELECT DISTINCT i.Id_Capsule FROM invitation i WHERE i.Id_Utilisateur = ? AND i.statut = \'accepted\'
         )',
        [$userId, $userId]
    );
}

/**
 * Retourne le nombre de capsules ouvertes accessibles par un utilisateur (propriétaire ou invité accepté).
 * @param int $userId
 * @return int
 */
function cv_capsule_count_open_accessible_for_user(int $userId): int
{
    return cv_db_fetch_value(
        'SELECT COUNT(DISTINCT c.Id_Capsule)
         FROM capsule c
         WHERE (c.Id_Utilisateur = ? OR c.Id_Capsule IN (
             SELECT DISTINCT i.Id_Capsule FROM invitation i WHERE i.Id_Utilisateur = ? AND i.statut = \'accepted\'
         ))
         AND c.date_ouverture IS NOT NULL
         AND c.date_ouverture <= NOW()',
        [$userId, $userId]
    );
}

/**
 * Compte le nombre d'invitations en attente pour un utilisateur.
 * @param int $userId
 * @return int
 */
function cv_invitation_count_pending_for_user(int $userId): int
{
    return cv_db_fetch_value(
        "SELECT COUNT(*) FROM invitation WHERE Id_Utilisateur = :user_id AND statut = 'pending'",
        [':user_id' => $userId]
    );
}

/**
 * Compte tous les souvenirs postés par un utilisateur.
 * @param int $userId
 * @return int
 */
function cv_souvenir_count_for_user(int $userId): int
{
    return cv_db_fetch_value(
        'SELECT COUNT(*) FROM souvenir WHERE Id_Utilisateur = :user_id',
        [':user_id' => $userId]
    );
}

/**
 * Compte les souvenirs accessibles (capsules ouvertes) pour un utilisateur.
 * @param int $userId
 * @return int
 */
function cv_souvenir_count_open_accessible_for_user(int $userId): int
{
    return cv_db_fetch_value(
        'SELECT COUNT(*)
         FROM souvenir s
         JOIN capsule c ON c.Id_Capsule = s.Id_Capsule
         WHERE (c.Id_Utilisateur = ? OR c.Id_Capsule IN (
             SELECT DISTINCT i.Id_Capsule FROM invitation i WHERE i.Id_Utilisateur = ? AND i.statut = \'accepted\'
         ))
         AND c.date_ouverture IS NOT NULL
         AND c.date_ouverture <= NOW()',
        [$userId, $userId]
    );
}

/**
 * Récupère les capsules récentes accessibles par l'utilisateur (limite configurable).
 * @param int $userId
 * @param int $limit
 * @return array
 */
function cv_capsule_recent_for_user(int $userId, int $limit = 3): array
{
    return cv_db_fetch_all(
        'SELECT c.Id_Capsule, c.titre, c.description, c.date_creation, c.date_ouverture, c.photo,
                (SELECT COUNT(*) FROM souvenir s WHERE s.Id_Capsule = c.Id_Capsule) AS souvenir_count,
                (SELECT COUNT(*) FROM invitation i WHERE i.Id_Capsule = c.Id_Capsule) AS invitation_count
         FROM capsule c
         WHERE c.Id_Utilisateur = ? OR c.Id_Capsule IN (
             SELECT DISTINCT i.Id_Capsule FROM invitation i WHERE i.Id_Utilisateur = ? AND i.statut = \'accepted\'
         )
         ORDER BY c.date_creation DESC, c.Id_Capsule DESC
         LIMIT ' . (int)$limit,
        [$userId, $userId]
    );
}

/**
 * Récupère les invitations récentes en attente pour un utilisateur.
 * @param int $userId
 * @param int $limit
 * @return array
 */
function cv_invitation_recent_pending_for_user(int $userId, int $limit = 5): array
{
    return cv_db_fetch_all(
        'SELECT i.Id_Invitation, i.statut, i.date_envoi, c.titre, c.date_ouverture, u.nom AS owner_name
         FROM invitation i
         INNER JOIN capsule c ON c.Id_Capsule = i.Id_Capsule
         INNER JOIN utilisateur u ON u.Id_Utilisateur = c.Id_Utilisateur
         WHERE i.Id_Utilisateur = :user_id AND i.statut = :statut
         ORDER BY i.date_envoi DESC, i.Id_Invitation DESC
         LIMIT ' . (int)$limit,
        [':user_id' => $userId, ':statut' => 'pending']
    );
}

/**
 * Récupère une capsule par son identifiant, avec le nom du propriétaire.
 * @param int $capsuleId
 * @return array|null
 */
function cv_capsule_get_by_id(int $capsuleId): ?array
{
    return cv_db_fetch_one(
        'SELECT c.Id_Capsule, c.titre, c.description, c.photo, c.date_creation, c.date_ouverture, c.Id_Utilisateur, u.nom AS owner_name
         FROM capsule c
         INNER JOIN utilisateur u ON u.Id_Utilisateur = c.Id_Utilisateur
         WHERE c.Id_Capsule = :capsule_id
         LIMIT 1',
        [':capsule_id' => $capsuleId]
    );
}

/**
 * Vérifie si un utilisateur est le propriétaire d'une capsule.
 * @param int $capsuleId
 * @param int $userId
 * @return bool
 */
function cv_capsule_user_is_owner(int $capsuleId, int $userId): bool
{
    return cv_db_fetch_value(
        'SELECT COUNT(*) FROM capsule WHERE Id_Capsule = :capsule_id AND Id_Utilisateur = :user_id LIMIT 1',
        [':capsule_id' => $capsuleId, ':user_id' => $userId]
    ) > 0;
}

/**
 * Indique si l'utilisateur a accès à la capsule (invité accepté ou propriétaire).
 * @param int $capsuleId
 * @param int $userId
 * @return bool
 */
function cv_capsule_user_has_access(int $capsuleId, int $userId): bool
{
    return cv_db_fetch_value(
        'SELECT COUNT(*) FROM invitation WHERE Id_Capsule = :capsule_id AND Id_Utilisateur = :user_id AND statut = :status',
        [':capsule_id' => $capsuleId, ':user_id' => $userId, ':status' => 'accepted']
    ) > 0 || cv_capsule_user_is_owner($capsuleId, $userId);
}

/**
 * Retourne l'ID de la première capsule accessible pour l'utilisateur (la plus récente).
 * @param int $userId
 * @return int
 */
function cv_capsule_get_first_accessible_for_user(int $userId): int
{
    return cv_db_fetch_value(
        'SELECT DISTINCT c.Id_Capsule FROM capsule c
         LEFT JOIN invitation inv ON c.Id_Capsule = inv.Id_Capsule AND inv.Id_Utilisateur = :user_id AND inv.statut = :status
         WHERE c.Id_Utilisateur = :user_id OR inv.Id_Utilisateur = :user_id
         ORDER BY c.date_creation DESC, c.Id_Capsule DESC LIMIT 1',
        [':user_id' => $userId, ':status' => 'accepted']
    );
}

/**
 * Récupère la liste des membres (acceptés) d'une capsule.
 * @param int $capsuleId
 * @return array
 */
function cv_capsule_get_members(int $capsuleId): array
{
    return cv_db_fetch_all(
        'SELECT u.nom, u.email, i.statut
         FROM invitation i
         INNER JOIN utilisateur u ON u.Id_Utilisateur = i.Id_Utilisateur
         WHERE i.Id_Capsule = :capsule_id AND i.statut = :status
         ORDER BY i.date_envoi DESC, i.Id_Invitation DESC',
        [':capsule_id' => $capsuleId, ':status' => 'accepted']
    );
}

/**
 * Récupère tous les souvenirs d'une capsule (triés par date de publication).
 * @param int $capsuleId
 * @return array
 */
function cv_capsule_get_souvenirs(int $capsuleId): array
{
    return cv_db_fetch_all(
        'SELECT s.Id_Souvenir, s.type, s.donnee, s.date_poste, u.nom AS auteur_nom
         FROM souvenir s
         INNER JOIN utilisateur u ON u.Id_Utilisateur = s.Id_Utilisateur
         WHERE s.Id_Capsule = :capsule_id
         ORDER BY date_poste DESC, Id_Souvenir DESC',
        [':capsule_id' => $capsuleId]
    );
}

/**
 * Calcule le nombre de membres à afficher (propriétaire + membres acceptés).
 * @param int $capsuleId
 * @return int
 */
function cv_capsule_count_member_display(int $capsuleId): int
{
    return 1 + cv_db_fetch_value(
        "SELECT COUNT(*) FROM invitation WHERE Id_Capsule = :capsule_id AND statut = 'accepted'",
        [':capsule_id' => $capsuleId]
    );
}

/**
 * Recherche un utilisateur par email (insensible à la casse).
 * @param string $email
 * @return array|null
 */
function cv_user_find_by_email(string $email): ?array
{
    return cv_db_fetch_one(
        'SELECT Id_Utilisateur, nom, email, statut FROM utilisateur WHERE LOWER(email) = LOWER(:email) LIMIT 1',
        [':email' => $email]
    );
}

/**
 * Indique si une invitation existe déjà pour un utilisateur et une capsule.
 * @param int $capsuleId
 * @param int $userId
 * @return bool
 */
function cv_invitation_exists_for_capsule(int $capsuleId, int $userId): bool
{
    return cv_db_fetch_value(
        'SELECT COUNT(*) FROM invitation WHERE Id_Capsule = :capsule_id AND Id_Utilisateur = :user_id LIMIT 1',
        [':capsule_id' => $capsuleId, ':user_id' => $userId]
    ) > 0;
}

/**
 * Crée une nouvelle capsule en base et retourne son ID.
 * @param array $data (titre, description, photo, date_creation, date_ouverture, user_id)
 * @return int
 */
function cv_capsule_create(array $data): int
{
    global $pdo;

    $stmt = $pdo->prepare('INSERT INTO capsule (titre, description, photo, date_creation, date_ouverture, Id_Utilisateur) VALUES (:titre, :description, :photo, :date_creation, :date_ouverture, :user_id)');
    $stmt->execute([
        ':titre' => $data['titre'],
        ':description' => $data['description'],
        ':photo' => $data['photo'],
        ':date_creation' => $data['date_creation'],
        ':date_ouverture' => $data['date_ouverture'],
        ':user_id' => $data['user_id'],
    ]);

    return (int)$pdo->lastInsertId();
}

/**
 * Crée une invitation pour une capsule et un utilisateur.
 * @param int $capsuleId
 * @param int $userId
 * @param string $status
 * @param string|null $dateEnvoi
 * @return void
 */
function cv_invitation_create(int $capsuleId, int $userId, string $status = 'pending', ?string $dateEnvoi = null): void
{
    global $pdo;

    $stmt = $pdo->prepare('INSERT INTO invitation (statut, date_envoi, Id_Capsule, Id_Utilisateur) VALUES (:statut, :date_envoi, :capsule_id, :user_id)');
    $stmt->execute([
        ':statut' => $status,
        ':date_envoi' => $dateEnvoi ?? cv_now(),
        ':capsule_id' => $capsuleId,
        ':user_id' => $userId,
    ]);
}

/**
 * Ajoute un souvenir (message ou photo) à une capsule.
 * @param int $capsuleId
 * @param int $userId
 * @param string $type
 * @param string $donnee
 * @param string|null $datePoste
 * @return void
 */
function cv_souvenir_create(int $capsuleId, int $userId, string $type, string $donnee, ?string $datePoste = null): void
{
    global $pdo;

    $stmt = $pdo->prepare('INSERT INTO souvenir (type, donnee, date_poste, Id_Utilisateur, Id_Capsule) VALUES (:type, :donnee, :date_poste, :user_id, :capsule_id)');
    $stmt->execute([
        ':type' => $type,
        ':donnee' => $donnee,
        ':date_poste' => $datePoste ?? cv_now(),
        ':user_id' => $userId,
        ':capsule_id' => $capsuleId,
    ]);
}

/**
 * Supprime une capsule et ses données associées (invitations, souvenirs) si l'utilisateur est propriétaire.
 * @param int $capsuleId
 * @param int $userId
 * @return void
 */
function cv_capsule_delete_owned(int $capsuleId, int $userId): void
{
    global $pdo;

    $stmt = $pdo->prepare('DELETE FROM invitation WHERE Id_Capsule = :capsule_id');
    $stmt->execute([':capsule_id' => $capsuleId]);

    $stmt = $pdo->prepare('DELETE FROM souvenir WHERE Id_Capsule = :capsule_id');
    $stmt->execute([':capsule_id' => $capsuleId]);

    $stmt = $pdo->prepare('DELETE FROM capsule WHERE Id_Capsule = :capsule_id AND Id_Utilisateur = :user_id');
    $stmt->execute([':capsule_id' => $capsuleId, ':user_id' => $userId]);
}

/**
 * Met à jour le statut d'une invitation pour un utilisateur.
 * Les invitations refusées sont supprimées de la base.
 * Retourne true si une ligne a été modifiée.
 * @param int $invitationId
 * @param int $userId
 * @param string $status
 * @return bool
 */
function cv_invitation_update_status_for_user(int $invitationId, int $userId, string $status): bool
{
    global $pdo;

    if ($status === 'declined') {
        $stmt = $pdo->prepare('DELETE FROM invitation WHERE Id_Invitation = :invitation_id AND Id_Utilisateur = :user_id');
        $stmt->execute([
            ':invitation_id' => $invitationId,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    $stmt = $pdo->prepare('UPDATE invitation SET statut = :status WHERE Id_Invitation = :invitation_id AND Id_Utilisateur = :user_id');
    $stmt->execute([
        ':status' => $status,
        ':invitation_id' => $invitationId,
        ':user_id' => $userId,
    ]);

    return $stmt->rowCount() > 0;
}
