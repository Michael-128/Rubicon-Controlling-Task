<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Customer;

class AppFixtures extends Fixture
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    private function loadCsv(string $path, ObjectManager $manager): void {
        $isHeaderRow = true;

        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, null, "|")) !== FALSE) {
                if($isHeaderRow) {
                    $isHeaderRow = false;
                    continue;
                }

                $customer = new Customer();
                $customer->setCustomer($data[0]);
                $customer->setCountry($data[1]);
                $customer->setOrderName($data[2]);
                $customer->setStatus($data[3]);
                $customer->setGroupName($data[4]);
                $customer->setSource("csv");

                $manager->persist($customer);
            }
            fclose($handle);
        }
    }

    private function loadLdif(string $path, ObjectManager $manager): void {
        if (($content = file_get_contents($path)) !== FALSE) {
            // Split content into entries (separated by blank lines)
            $entries = preg_split("/\n\s*\n/", $content);
            
            foreach ($entries as $entry) {
                if (empty(trim($entry))) continue;
                
                // Parse LDIF entry into key-value pairs
                $data = [];
                foreach (explode("\n", $entry) as $line) {
                    if (preg_match('/^(\w+):\s*(.*)$/', $line, $matches)) {
                        $data[$matches[1]] = $matches[2];
                    }
                }
                
                // Skip if required fields are missing
                if (!isset($data['Customer'], $data['Country'], $data['Order'], 
                          $data['Status'], $data['Group'])) {
                    continue;
                }

                $customer = new Customer();
                $customer->setCustomer($data['Customer']);
                $customer->setCountry($data['Country']);
                $customer->setOrderName($data['Order']);
                $customer->setStatus($data['Status']);
                $customer->setGroupName($data['Group']);
                $customer->setSource("ldif");

                $manager->persist($customer);
            }
        }
    }

    private function loadJson(string $path, ObjectManager $manager): void {
        if (($content = file_get_contents($path)) !== FALSE) {
            $data = json_decode($content, true);
            
            // Check if we have the expected structure
            if (!isset($data['cols']) || !isset($data['data'])) {
                return;
            }

            // Map column names to their indices
            $colMap = array_flip($data['cols']);

            foreach ($data['data'] as $row) {
                $customer = new Customer();
                $customer->setCustomer($row[$colMap['Customer']]);
                $customer->setCountry($row[$colMap['Country']]);
                $customer->setOrderName($row[$colMap['Order']]);
                $customer->setStatus($row[$colMap['Status']]);
                $customer->setGroupName($row[$colMap['Group']]);
                $customer->setSource("json");

                $manager->persist($customer);
            }
        }
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadCsv($this->projectDir . '/assets/data/data.csv', $manager);
        $this->loadLdif($this->projectDir . '/assets/data/data.ldif', $manager);
        $this->loadJson($this->projectDir . '/assets/data/data.json', $manager);

        $manager->flush();
    }
}
