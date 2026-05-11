<?php

require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';

// Helper encodage UTF-8 → Latin-1 pour FPDF
function u(string $txt): string {
    return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $txt) ?: $txt;
}

class PvPDF extends FPDF
{
    private const ML        = 22;
    private const MR        = 22;
    private const LINE_H    = 5.5;
    private const SECTION   = 7.0;
    private const FONT_BODY = 9;
    private const FONT_LBL  = 9.5;

    private float $cw;

    // Chemins absolus vers les logos
    private string $logoENSAH;
    private string $logoUAE;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->cw = 210 - self::ML - self::MR;
        $this->SetMargins(self::ML, 18, self::MR);
        $this->SetAutoPageBreak(false);
        $this->AddPage();
        $this->SetFont('Helvetica', '', self::FONT_BODY);

        // Chemins logos — place tes images ici
        $this->logoENSAH = __DIR__ . '/../assets/images/ensah.png';
        $this->logoUAE   = __DIR__ . '/../assets/images/uae.png';
    }

    public function remplir(array $data): void
    {
        $this->_entete();
        $this->_titreDepartement();
        $this->_nomEtudiant($data);
        $this->_filiere($data['filiere'] ?? '');
        $this->_intituleRapport($data['titre_pfe'] ?? ($data['intitule_rapport'] ?? ''));
        $this->_encadrant($data['encadrant_nom'] ?? '', $data['encadrant_prenom'] ?? '');
        $this->_jury($data['jury'] ?? []);
        $this->_notes();
        $this->_tableauMoyenne();
        $this->_signatures();
    }

    // =========================================================================
    // EN-TÊTE avec logos réels
    // =========================================================================
    private function _entete(): void
    {
        $logoH = 18;
        $logoW = 22;
        $yLogo = 18;

        // Logo UAE (gauche)
        if (file_exists($this->logoUAE)) {
            $this->Image($this->logoUAE, self::ML, $yLogo, $logoW, $logoH);
        } else {
            $this->Rect(self::ML, $yLogo, $logoW, $logoH);
            $this->SetFont('Helvetica', '', 5.5);
            $this->SetXY(self::ML, $yLogo + 7);
            $this->Cell($logoW, 4, 'LOGO UAE', 0, 0, 'C');
        }

        // Logo ENSAH (droite)
        $xRight = 210 - self::MR - $logoW;
        if (file_exists($this->logoENSAH)) {
            $this->Image($this->logoENSAH, $xRight, $yLogo, $logoW, $logoH);
        } else {
            $this->Rect($xRight, $yLogo, $logoW, $logoH);
            $this->SetFont('Helvetica', '', 5.5);
            $this->SetXY($xRight, $yLogo + 7);
            $this->Cell($logoW, 4, 'ENSAH', 0, 0, 'C');
        }

        // Texte université centré
        $this->SetFont('Helvetica', '', 8.5);
        $this->SetXY(self::ML, 21);
        $this->Cell($this->cw, 5, u('UNIVERSITE ABDELMALEK ESSAADI'), 0, 0, 'C');

        $this->SetFont('Helvetica', '', 7.5);
        $this->SetXY(self::ML, 26);
        $this->Cell(
            $this->cw, 4,
            u("Ecole Nationale des Sciences Appliquées d'Al-Hoceima - Maroc"),
            0, 1, 'C'
        );

        $this->Ln(3);
    }

    // =========================================================================
    // TITRE DÉPARTEMENT
    // =========================================================================
    private function _titreDepartement(): void
    {
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(
            $this->cw, 5,
            u('Département de Mathématiques et Informatique'),
            0, 1, 'C'
        );

        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(
            $this->cw, 4.5,
            u("Fiche d'évaluation du Projet de Fin d'Étude"),
            0, 1, 'C'
        );
        $this->Cell(
            $this->cw, 4.5,
            u('Année Universitaire : 2023-2024'),
            0, 1, 'C'
        );
        $this->Ln(5);
    }

    // =========================================================================
    // NOM ÉTUDIANT
    // =========================================================================
    private function _nomEtudiant(array $data): void
    {
        $this->_labelSouligne(u("Nom - Prénom de l'élève ingénieur :"));
        $this->Ln(self::LINE_H);

        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML);
        $this->Cell(6, self::LINE_H, '-');
        $nom = strtoupper($data['etudiant_nom'] ?? '')
             . ' '
             . ucfirst(strtolower($data['etudiant_prenom'] ?? ''));
        $this->Cell(0, self::LINE_H, u($nom), 'B', 1);
        $this->Ln(self::SECTION - self::LINE_H);
    }

    // =========================================================================
    // FILIÈRE — 3 filières : TDIA, ID, GI
    // =========================================================================
    private function _filiere(string $filiere): void
    {
        $this->_labelSouligne(u('Filière :'));
        $this->Ln(self::LINE_H + 1);

        $this->SetFont('Helvetica', '', self::FONT_BODY);

        $filieres = [
            'TDIA' => u('Transformation Digitale & Intelligence Artificielle'),
            'ID'   => u('Ingénierie des Données'),
            'GI'   => u('Génie Informatique'),
        ];

        $x     = self::ML;
        $y     = $this->GetY();
        $boxSz = 3.5;
        $gap   = 4;

        foreach ($filieres as $code => $label) {
            // Case à cocher
            $this->_caseCocher($x, $y + 0.8, $filiere === $code, $boxSz);
            $this->SetXY($x + $boxSz + 1.5, $y);
            $this->Cell(0, self::LINE_H, $label, 0, 1);
            $y = $this->GetY() + 0.5;
        }

        $this->Ln(self::SECTION - self::LINE_H * 0.5);
    }

    // =========================================================================
    // INTITULÉ DU RAPPORT
    // =========================================================================
    private function _intituleRapport(string $intitule): void
    {
        $this->_labelSouligne(u('Intitulé du rapport :'));
        $this->Ln(self::LINE_H);

        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML);
        $this->Cell(6, self::LINE_H, '-');
        $this->MultiCell($this->cw - 6, self::LINE_H, u($intitule), 'B', 'L');
        $this->Ln(self::SECTION - self::LINE_H);
    }

    // =========================================================================
    // ENCADRANT
    // =========================================================================
    private function _encadrant(string $nom, string $prenom): void
    {
        $this->_labelSouligne(u("L'encadrant (e) interne:"));
        $this->Ln(self::LINE_H);

        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML);
        $this->Cell(6,  self::LINE_H, '-');
        $this->Cell(8,  self::LINE_H, 'Pr.');
        $this->Cell(0,  self::LINE_H, u(strtoupper($nom) . ' ' . $prenom), 'B', 1);
        $this->Ln(self::SECTION - self::LINE_H);
    }

    // =========================================================================
    // JURY — rempli depuis BDD, lignes vides si données absentes
    // =========================================================================
    private function _jury(array $jury): void
    {
        $this->_labelSouligne(u('Membres du jury :'));
        $this->Ln(self::LINE_H);

        // Compléter jusqu'à 3 entrées vides si jury incomplet
        while (count($jury) < 3) {
            $jury[] = ['nom' => '', 'prenom' => '', 'role' => ''];
        }

        $this->SetFont('Helvetica', '', self::FONT_BODY);

        foreach (array_slice($jury, 0, 3) as $membre) {
            $this->SetX(self::ML);
            $this->Cell(6,  self::LINE_H, '-');
            $this->Cell(8,  self::LINE_H, 'Pr.');

            $nom_m = !empty($membre['nom'])
                ? u(strtoupper($membre['nom']) . ' ' . ($membre['prenom'] ?? ''))
                : '';

            // Rôle : Président ou Rapporteur depuis la BDD
            $role = !empty($membre['role']) ? u($membre['role']) : '';

            // Nom sur ligne avec soulignement
            $this->Cell(100, self::LINE_H, $nom_m, 'B');

            // Rôle aligné à droite
            $role_w = $this->GetStringWidth($role) + 2;
            $this->SetX(210 - self::MR - $role_w);
            $this->Cell($role_w, self::LINE_H, $role, 0, 1);
        }

        // Note informative si jury vide
        if (empty(array_filter(array_column($jury, 'nom')))) {
            $this->SetFont('Helvetica', 'I', 7);
            $this->SetTextColor(150, 150, 150);
            $this->SetX(self::ML);
            $this->Cell(
                0, self::LINE_H,
                u('(Les membres du jury seront renseignés après affectation)'),
                0, 1
            );
            $this->SetTextColor(0, 0, 0);
        }

        $this->Ln(self::SECTION - self::LINE_H);
    }

    // =========================================================================
    // NOTES
    // =========================================================================
    private function _notes(): void
    {
        // Note du Contenu
        $this->SetFont('Helvetica', 'B', self::FONT_LBL);
        $x = $this->GetX();
        $y = $this->GetY();
        $label = u('Note du Contenu');
        $this->Cell(0, self::LINE_H, $label, 0, 0);
        $this->_souligner($x, $y, $label, self::FONT_LBL);

        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetX(self::ML + $this->GetStringWidth($label) + 2);
        $this->Cell(
            0, self::LINE_H,
            u(" (En prenant en compte l'appréciation de l'entreprise)"),
            0, 1
        );

        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML + 5);
        $this->Cell(0, self::LINE_H, 'C  =', 0, 1);
        $this->Ln(self::SECTION - self::LINE_H);

        // Note du Mémoire
        $this->_labelSouligne(u('Note du Mémoire'));
        $this->Ln(self::LINE_H);
        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML + 5);
        $this->Cell(0, self::LINE_H, 'M  =', 0, 1);
        $this->Ln(self::SECTION - self::LINE_H);

        // Note de la Soutenance
        $this->_labelSouligne(u('Note de la Soutenance'));
        $this->Ln(self::LINE_H);
        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML + 5);
        $this->Cell(0, self::LINE_H, 'S  =', 0, 1);
        $this->Ln(self::SECTION - self::LINE_H * 0.5);
    }

    // =========================================================================
    // TABLEAU MOYENNE
    // =========================================================================
    private function _tableauMoyenne(): void
    {
        $this->SetFillColor(74, 74, 74);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetX(self::ML);
        $this->Cell($this->cw, 6.5, 'MOYENNE', 1, 1, 'C', true);

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetX(self::ML);
        $this->Cell(
            $this->cw, 6.5,
            u('Moyenne  = C * 0,5 + M * 0,2 + S * 0,3  ='),
            1, 1, 'L', true
        );

        $this->Ln(self::SECTION);
    }

    // =========================================================================
    // SIGNATURES
    // =========================================================================
    private function _signatures(): void
    {
        $this->SetFont('Helvetica', '', self::FONT_BODY);
        $this->SetX(self::ML);
        $this->Cell(0, self::LINE_H, u('Le :  ................................'), 0, 1);
        $this->Ln(2);

        $this->SetX(self::ML);
        $this->Cell(0, self::LINE_H, u('Signature des membres du jury :'), 0, 1);
        $this->Ln(self::LINE_H);

        $col_w = $this->cw / 3;
        for ($i = 0; $i < 3; $i++) {
            $this->SetX(self::ML + $i * $col_w);
            $this->Cell(8, self::LINE_H, 'Pr.');
            $this->Cell($col_w - 10, self::LINE_H, '', 'B');
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function _labelSouligne(string $txt, float $size = self::FONT_LBL): void
    {
        $this->SetFont('Helvetica', 'B', $size);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Cell(0, self::LINE_H, $txt, 0, 0);
        $this->_souligner($x, $y, $txt, $size);
    }

    private function _souligner(float $x, float $y, string $txt, float $size): void
    {
        $w = $this->GetStringWidth($txt);
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(0, 0, 0);
        $this->Line($x, $y + self::LINE_H - 0.8, $x + $w, $y + self::LINE_H - 0.8);
    }

    private function _caseCocher(float $x, float $y, bool $checked, float $size = 3.5): void
    {
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(0, 0, 0);
        $this->Rect($x, $y, $size, $size);
        if ($checked) {
            $this->Line($x + 0.5, $y + 0.5, $x + $size - 0.5, $y + $size - 0.5);
            $this->Line($x + $size - 0.5, $y + 0.5, $x + 0.5, $y + $size - 0.5);
        }
    }
}

// =============================================================================
// CLASSE SERVICE
// =============================================================================
class PvGenerator
{
    public static function generer(array $data, string $outputPath): void
    {
        $pdf = new PvPDF();
        $pdf->remplir($data);
        $pdf->Output('F', $outputPath);
    }

    public static function envoyer(array $data, string $filename): void
    {
        $pdf = new PvPDF();
        $pdf->remplir($data);
        $pdf->Output('D', $filename);
        exit;
    }
}