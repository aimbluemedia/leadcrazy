<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Starting points for the free lead form builder on /how-it-works.
 *
 * One click on a trade fills in sensible services, budget ranges and a
 * suggested offer, which the visitor then edits. Nothing here is binding:
 * every list is the visitor's to change before publishing.
 */
final class Trades
{
    /**
     * @var array<string,array{label:string,noun:string,services:list<string>,budgets:list<string>,offer:array{title:string,body:string}}>
     */
    public const ALL = [
        'landscaping' => [
            'label' => 'Landscaping', 'noun' => 'Landscape',
            'services' => ['Pavers', 'Artificial Turf', 'Irrigation', 'Outdoor Lighting', 'Plants & Trees', 'Fire Pits', 'Patios & Walkways', 'New Lawn Install'],
            'budgets' => ['Under $2,500', '$2,500 - $5,000', '$5,000 - $10,000', '$10,000 - $25,000', '$25,000+'],
            'offer' => ['title' => 'Free Design Consultation', 'body' => 'Book this month and get a free backyard design consultation with any project over $5,000.'],
        ],
        'hvac' => [
            'label' => 'HVAC', 'noun' => 'HVAC',
            'services' => ['AC Repair', 'AC Installation', 'Furnace Repair', 'Heat Pump', 'Duct Cleaning', 'Thermostat Install', 'Maintenance Plan', 'Indoor Air Quality'],
            'budgets' => ['Under $500', '$500 - $2,000', '$2,000 - $7,500', '$7,500 - $15,000', '$15,000+'],
            'offer' => ['title' => '$79 Tune-Up Special', 'body' => 'Full system check, filter change and cleaning before the season starts.'],
        ],
        'plumbing' => [
            'label' => 'Plumbing', 'noun' => 'Plumbing',
            'services' => ['Leak Repair', 'Drain Cleaning', 'Water Heater', 'Tankless Water Heater', 'Repiping', 'Fixture Install', 'Sewer Line', 'Water Softener'],
            'budgets' => ['Under $300', '$300 - $1,000', '$1,000 - $5,000', '$5,000 - $15,000', '$15,000+'],
            'offer' => ['title' => '$50 Off Any Repair', 'body' => 'Mention this offer when we call and save $50 on any repair over $250.'],
        ],
        'roofing' => [
            'label' => 'Roofing', 'noun' => 'Roofing',
            'services' => ['Roof Repair', 'Roof Replacement', 'Roof Inspection', 'Storm Damage', 'Gutters', 'Skylights', 'Roof Coatings'],
            'budgets' => ['Under $1,000', '$1,000 - $5,000', '$5,000 - $15,000', '$15,000 - $30,000', '$30,000+'],
            'offer' => ['title' => 'Free Roof Inspection', 'body' => 'A full inspection with photos and a written report, no obligation.'],
        ],
        'remodeling' => [
            'label' => 'Remodeling', 'noun' => 'Remodeling',
            'services' => ['Kitchen Remodel', 'Bathroom Remodel', 'Flooring', 'Cabinets', 'Countertops', 'Room Addition', 'Whole-Home Remodel'],
            'budgets' => ['Under $10,000', '$10,000 - $25,000', '$25,000 - $50,000', '$50,000 - $100,000', '$100,000+'],
            'offer' => ['title' => 'Free In-Home Design Consultation', 'body' => 'Meet with our designer at your home and leave with a plan and a clear quote.'],
        ],
        'cleaning' => [
            'label' => 'Cleaning', 'noun' => 'Cleaning',
            'services' => ['Home Cleaning', 'Deep Cleaning', 'Move-In / Move-Out', 'Office Cleaning', 'Carpet Cleaning', 'Window Cleaning'],
            'budgets' => ['Under $150', '$150 - $300', '$300 - $600', '$600+', 'Recurring service'],
            'offer' => ['title' => '20% Off Your First Clean', 'body' => 'New customers save 20% on their first visit, any size home.'],
        ],
        'painting' => [
            'label' => 'Painting', 'noun' => 'Painting',
            'services' => ['Interior Painting', 'Exterior Painting', 'Cabinet Painting', 'Drywall Repair', 'Deck & Fence Staining', 'Commercial Painting'],
            'budgets' => ['Under $1,000', '$1,000 - $3,000', '$3,000 - $7,500', '$7,500 - $15,000', '$15,000+'],
            'offer' => ['title' => 'Free Color Consultation', 'body' => 'Not sure on colors? We bring samples and help you choose, free with any quote.'],
        ],
        'pools' => [
            'label' => 'Pools', 'noun' => 'Pool',
            'services' => ['Pool Cleaning', 'Pool Repair', 'Equipment Install', 'Resurfacing', 'New Pool Build', 'Pool Remodel'],
            'budgets' => ['Under $500', '$500 - $5,000', '$5,000 - $20,000', '$20,000 - $60,000', '$60,000+'],
            'offer' => ['title' => 'First Month of Cleaning 50% Off', 'body' => 'Sign up for weekly service and your first month is half price.'],
        ],
        'electrical' => [
            'label' => 'Electrical', 'noun' => 'Electrical',
            'services' => ['Panel Upgrade', 'Lighting Install', 'EV Charger', 'Outlets & Switches', 'Ceiling Fans', 'Generator', 'Rewiring'],
            'budgets' => ['Under $300', '$300 - $1,500', '$1,500 - $5,000', '$5,000 - $15,000', '$15,000+'],
            'offer' => ['title' => '$50 Off EV Charger Install', 'body' => 'Save $50 on a Level 2 home charger installation.'],
        ],
        'other' => [
            'label' => 'Other', 'noun' => 'Service',
            'services' => ['Installation', 'Repair', 'Maintenance', 'Consultation'],
            'budgets' => ['Under $500', '$500 - $2,500', '$2,500 - $10,000', '$10,000+'],
            'offer' => ['title' => 'Free Estimate', 'body' => 'Tell us about your project and get a free, no-obligation estimate.'],
        ],
    ];

    public static function exists(string $key): bool
    {
        return isset(self::ALL[$key]);
    }

    /** @return array<string,mixed> */
    public static function get(string $key): array
    {
        return self::ALL[$key] ?? self::ALL['other'];
    }
}
