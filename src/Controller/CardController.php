<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CardController extends AbstractController
{
    #[Route('/cards/set-overview')]
    public function setOverviewPage(
    ): Response {
        try {
            $sets = [
                ['id' => 'SUP', 'name' => 'Super Slam', 'logo' => 'super_slam_logo.original.png'],
                ['id' => 'MPG', 'name' => 'Mastery Pack Guardian', 'logo' => 'mastery_pack_guardian_logo_1.original.png'],
                ['id' => 'SEA', 'name' => 'High Seas', 'logo' => 'high_seas_logo_noglow.original.png'],
                ['id' => 'HNT', 'name' => 'The Hunted', 'logo' => 'the_hunted_logo.original.png'],
                ['id' => 'ROS', 'name' => 'Rosetta', 'logo' => 'logo_rosetta_full.original.png'],
                ['id' => 'MST', 'name' => 'Part the Mistveil', 'logo' => 'logo_mst.original.png'],
                ['id' => 'HVY', 'name' => 'Heavy Hitters', 'logo' => 'heavy_hitters_logo.original.png'],
                ['id' => 'EVO', 'name' => 'Bright Lights', 'logo' => 'bright_lights.original.png'],
                ['id' => 'DTD', 'name' => 'Dusk till Dawn', 'logo' => 'dusk_till_dawn_logo.original.png'],
                ['id' => 'OUT', 'name' => 'Outsiders', 'logo' => 'outsiders23012.original.png'],
                ['id' => 'DYN', 'name' => 'Dynasty', 'logo' => 'dynasty_logo_re.original.png'],
                ['id' => '1HP', 'name' => 'History Pack 1', 'logo' => '1hp_stacked.original.png'],
                ['id' => 'UPR', 'name' => 'Uprising', 'logo' => 'uprising.original.png'],
                ['id' => 'EVR', 'name' => 'Everfest', 'logo' => 'everfest.original.png'],
                ['id' => 'ELE', 'name' => 'Tales of Aria', 'logo' => 'toa_logo.original.png'],
                ['id' => 'MON', 'name' => 'Monarch', 'logo' => 'monarch.original.png'],
                ['id' => 'CRU', 'name' => 'Crucible', 'logo' => 'cru_logo_stacked.original.png'],
                ['id' => 'ARC', 'name' => 'Arcane Rising', 'logo' => 'ARC.original.png'],
                ['id' => 'WTR', 'name' => 'Welcome to Rathe', 'logo' => 'wtr_logo.original.png']
            ];

            return $this->render('card/set_overview.html.twig', [
                'sets' => $sets,
            ]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }

    #[Route('/cards/class-overview')]
    public function classOverviewPage(
    ): Response {
        try {
            $classes = [
                ['name' => 'Generic', 'slug' => 'generic', 'image' => 'generic.png'],
                ['name' => 'Warrior', 'slug' => 'warrior', 'image' => 'warrior.png'],
                ['name' => 'Guardian', 'slug' => 'guardian', 'image' => 'guardian.png'],
                ['name' => 'Brute', 'slug' => 'brute', 'image' => 'brute.png'],
                ['name' => 'Ninja', 'slug' => 'ninja', 'image' => 'ninja.png'],
                ['name' => 'Wizard', 'slug' => 'wizard', 'image' => 'wizard.png'],
                ['name' => 'Illusionist', 'slug' => 'illusionist', 'image' => 'illusionist.png'],
                ['name' => 'Assassin', 'slug' => 'assassin', 'image' => 'assassin.png'],
                ['name' => 'Mechanologist', 'slug' => 'mechanologist', 'image' => 'mechanologist.png'],
                ['name' => 'Runeblade', 'slug' => 'runeblade', 'image' => 'runeblade.png'],
                ['name' => 'Ranger', 'slug' => 'ranger', 'image' => 'ranger.png'],
                ['name' => 'Necromancer', 'slug' => 'necromancer', 'image' => 'ranger.png'],
                ['name' => 'Pirate', 'slug' => 'pirate', 'image' => 'ranger.png'],
                ['name' => 'Bard', 'slug' => 'bard', 'image' => 'bard.png'],
                ['name' => 'Merchant', 'slug' => 'merchant', 'image' => 'merchant.png'],
                ['name' => 'Shapeshifter', 'slug' => 'shapeshifter', 'image' => 'shapeshifter.png'],
            ];

            return $this->render('card/class_overview.html.twig', [
                'classes' => $classes,
            ]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}