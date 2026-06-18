<?php

namespace App\Services;

use PDO;

class PaymentReferenceGenerator
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Identifie de manière insensible à la casse si le mode de paiement correspond à des espèces.
     */
    public static function isCashMethod(?string $method): bool
    {
        if (empty($method)) {
            return false;
        }
        $clean = strtr(mb_strtolower(trim($method), 'UTF-8'), [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e'
        ]);
        return in_array($clean, ['cash', 'espece', 'especes']);
    }

    /**
     * Génère une référence de paiement cryptographiquement sécurisée, unique et de longueur 20.
     */
    public function generateUniqueReference(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $alphabetLength = strlen($alphabet);

        do {
            $reference = '';
            for ($i = 0; $i < 20; $i++) {
                $reference .= $alphabet[random_int(0, $alphabetLength - 1)];
            }

            // Vérifier son existence dans la table payments
            $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE reference = ?");
            $stmt1->execute([$reference]);
            $existsLegacy = (int)$stmt1->fetchColumn() > 0;

            // Vérifier son existence dans la table student_payments
            $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM student_payments WHERE reference = ?");
            $stmt2->execute([$reference]);
            $existsStudent = (int)$stmt2->fetchColumn() > 0;

        } while ($existsLegacy || $existsStudent);

        return $reference;
    }
}
