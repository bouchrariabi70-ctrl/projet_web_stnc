<?php
 
require_once __DIR__ . '/PvGenerator.php';
 
/**
 * PvController
 *
 * Gère :
 *   - La vue index : étudiants groupés par encadrant
 *   - La génération automatique des PVs (remplissage depuis BDD)
 *   - Le téléchargement PDF :
 *       scope=tous        → tous les PVs (ZIP)
 *       scope=encadrant   → PVs d'un encadrant (ZIP ou PDF fusionné)
 *       scope=etudiant    → PV d'un seul étudiant
 *
 * Le remplissage des notes (C, M, S) reste MANUEL après impression.
 */
class PvController
{
    private PDO $db;
 
    /** Dossier de stockage des PDFs générés (chemin absolu) */
    private string $pvDir;
 
   public function __construct(PDO $db)
{
    $this->db = $db;
    
    // Chemin absolu sans dépendre de realpath (qui échoue si le dossier n'existe pas)
    $this->pvDir = __DIR__ . '/../../storage/pvs/';
    
    // Créer le dossier récursivement s'il n'existe pas
    if (!is_dir($this->pvDir)) {
        mkdir($this->pvDir, 0755, true);
    }
    
    // Normaliser le chemin après création
    $this->pvDir = realpath($this->pvDir) . DIRECTORY_SEPARATOR;
}
    

    // =========================================================================
    // ACTION : index — liste des étudiants groupée par encadrant
    // =========================================================================
    public function index(): void
    {
        $stmt = $this->db->query("
            SELECT
                p.id                    AS encadrant_id,
                p.nom                   AS encadrant_nom,
                p.prenom                AS encadrant_prenom,

                e.id_etudiant           AS etudiant_id,
                e.nom                   AS etudiant_nom,
                e.prenom                AS etudiant_prenom,
                e.filiere,

                s.id_stnc,
                s.titre_pfe,
                s.date                  AS date_soutenance,
                s.heure_debut,
                s.heure_fin,

                sa.numero_salle,

                IF(pv.id IS NOT NULL, 1, 0) AS pv_existe

            FROM professeur p

            JOIN etudiant e
                ON e.id_prof = p.id

            LEFT JOIN soutenance s
                ON s.etudiant_id = e.id_etudiant

            LEFT JOIN salle sa
                ON sa.id_salle = s.id_salle

            LEFT JOIN pv
                ON pv.soutenance_id = s.id_stnc

            ORDER BY p.nom, p.prenom, e.nom, e.prenom
        ");

        // Structurer les données
        $encadrants = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $eid = $row['encadrant_id'];

            if (!isset($encadrants[$eid])) {

                $encadrants[$eid] = [
                    'id' => $eid,
                    'nom' => $row['encadrant_nom'],
                    'prenom' => $row['encadrant_prenom'],
                    'etudiants' => []
                ];
            }

            $encadrants[$eid]['etudiants'][] = $row;
        }

        require 'views/pv/index.php';
    }

    // =========================================================================
    // ACTION : genererPV
    // =========================================================================
    public function genererPV(int $etudiant_id): void
    {
        $data = $this->_getDataEtudiant($etudiant_id);

        if (!$data) {

            http_response_code(404);
            echo "Étudiant introuvable.";
            return;
        }

        $this->_genererPDF($data);

        if (!empty($data['id_stnc'])) {

            $stmt = $this->db->prepare("
                INSERT INTO pv (soutenance_id, note_contenu, note_memoire,
                                note_soutenance, moyenne, mention,
                                president_jury_id, date_signature)

                VALUES (:soutenance_id, 0, 0, 0, 0, 'Ajourné', 1, CURDATE())

                ON DUPLICATE KEY UPDATE
                    updated_at = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                'soutenance_id' => $data['id_stnc']
            ]);
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'PV généré avec succès.'
        ];

        header('Location: index.php?controller=pv&action=index');
        exit;
    }

    // =========================================================================
    // ACTION : telecharger
    // =========================================================================
    public function telecharger(): void
    {
        $scope = $_GET['scope'] ?? 'tous';

        $encadrantId = (int)($_GET['encadrant_id'] ?? 0);
        $etudiantId = (int)($_GET['etudiant_id'] ?? 0);

        switch ($scope) {

            case 'etudiant':

                if ($etudiantId <= 0) {
                    $this->_badRequest("etudiant_id requis");
                    return;
                }

                $this->_telechargerUnPV($etudiantId);
                break;

            case 'encadrant':

                if ($encadrantId <= 0) {
                    $this->_badRequest("encadrant_id requis");
                    return;
                }

                $this->_telechargerParEncadrant($encadrantId);
                break;

            default:
                $this->_telechargerTous();
                break;
        }
    }

    // =========================================================================
    // PRIVÉ : données étudiant
    // =========================================================================
    private function _getDataEtudiant(int $etudiant_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.id_etudiant           AS etudiant_id,
                e.nom                   AS etudiant_nom,
                e.prenom                AS etudiant_prenom,
                e.filiere,

                p.nom                   AS encadrant_nom,
                p.prenom                AS encadrant_prenom,

                s.id_stnc,
                s.titre_pfe,
                s.date,
                s.heure_debut,
                s.heure_fin,

                sa.numero_salle

            FROM etudiant e

            LEFT JOIN professeur p
                ON p.id = e.id_prof

            LEFT JOIN soutenance s
                ON s.etudiant_id = e.id_etudiant

            LEFT JOIN salle sa
                ON sa.id_salle = s.id_salle

            WHERE e.id_etudiant = ?
        ");

        $stmt->execute([$etudiant_id]);

        $base = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$base) {
            return null;
        }

        // Jury
        $stmt = $this->db->prepare("
            SELECT
                p.nom,
                p.prenom,
                j.role

            FROM jury j

            JOIN professeur p
                ON p.id = j.id_prof

            WHERE j.id_soutenance = ?

            ORDER BY j.role DESC
        ");

        $stmt->execute([$base['id_stnc']]);

        $jury = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_merge($base, [
            'jury' => $jury
        ]);
    }

    // =========================================================================
    // Génération PDF
    // =========================================================================
    private function _genererPDF(array $data): void
    {
        $chemin = $this->pvDir . $this->_nomFichier($data);

        PvGenerator::generer($data, $chemin);
    }

    private function _nomFichier(array $data): string
    {
        $nom = preg_replace(
            '/[^a-zA-Z0-9_\-]/',
            '_',
            $data['etudiant_nom'] . '_' . $data['etudiant_prenom']
        );

        return "pv_{$data['etudiant_id']}_{$nom}.pdf";
    }

    // =========================================================================
    // Télécharger un seul PV
    // =========================================================================
    public function _telechargerUnPV(int $etudiant_id): void
    {
        $data = $this->_getDataEtudiant($etudiant_id);

        if (!$data) {
            $this->_badRequest("Étudiant introuvable");
            return;
        }

        $chemin = $this->pvDir . $this->_nomFichier($data);

        if (!file_exists($chemin)) {
            $this->_genererPDF($data);
        }

        $this->_envoyerPDF($chemin, $this->_nomFichier($data));
    }

    // =========================================================================
    // Télécharger par encadrant
    // =========================================================================
    public function _telechargerParEncadrant(int $encadrant_id): void
    {
        $stmt = $this->db->prepare("
            SELECT id_etudiant
            FROM etudiant
            WHERE id_prof = ?
        ");

        $stmt->execute([$encadrant_id]);

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) {
            echo "Aucun étudiant.";
            return;
        }

        $stmt = $this->db->prepare("
            SELECT nom, prenom
            FROM professeur
            WHERE id = ?
        ");

        $stmt->execute([$encadrant_id]);

        $enc = $stmt->fetch(PDO::FETCH_ASSOC);

        $nomEnc = preg_replace(
            '/[^a-zA-Z0-9_\-]/',
            '_',
            $enc['nom'] ?? 'encadrant'
        );

        $fichiers = $this->_assurerPVs($ids);

        $this->_envoyerZip(
            $fichiers,
            "pvs_encadrant_{$nomEnc}.zip"
        );
    }

    // =========================================================================
    // Télécharger tous
    // =========================================================================
    public function _telechargerTous(): void
    {
        $stmt = $this->db->query("
            SELECT id_etudiant
            FROM etudiant
            ORDER BY nom, prenom
        ");

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $fichiers = $this->_assurerPVs($ids);

        $this->_envoyerZip($fichiers, "tous_les_pvs.zip");
    }

    // =========================================================================
    // Assurer génération PVs
    // =========================================================================
    private function _assurerPVs(array $etudiant_ids): array
    {
        $fichiers = [];

        foreach ($etudiant_ids as $id) {

            $data = $this->_getDataEtudiant((int)$id);

            if (!$data) {
                continue;
            }

            $chemin = $this->pvDir . $this->_nomFichier($data);

            if (!file_exists($chemin)) {
                $this->_genererPDF($data);
            }

            if (file_exists($chemin)) {
                $fichiers[] = $chemin;
            }
        }

        return $fichiers;
    }

    // =========================================================================
    // Envoyer PDF
    // =========================================================================
    private function _envoyerPDF(string $chemin, string $nom): void
    {
        if (!file_exists($chemin)) {

            http_response_code(404);
            echo "Fichier introuvable.";
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Content-Length: ' . filesize($chemin));

        readfile($chemin);
        exit;
    }

    // =========================================================================
    // Envoyer ZIP
    // =========================================================================
    private function _envoyerZip(array $fichiers, string $nom_zip): void
    {
        if (empty($fichiers)) {
            echo "Aucun PV disponible.";
            return;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pvs_') . '.zip';

        $zip = new ZipArchive();

        if ($zip->open($tmp, ZipArchive::CREATE) !== true) {

            http_response_code(500);
            echo "Erreur ZIP.";
            return;
        }

        foreach ($fichiers as $f) {
            $zip->addFile($f, basename($f));
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nom_zip . '"');
        header('Content-Length: ' . filesize($tmp));

        readfile($tmp);

        unlink($tmp);

        exit;
    }

    private function _badRequest(string $msg): void
    {
        http_response_code(400);
        echo $msg;
    }
}
?>