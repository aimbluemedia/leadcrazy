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
            'label' => 'HVAC', 'noun' => 'HVAC', 'phrase' => 'HVAC',
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
        'solar' => [
            'label' => 'Solar', 'noun' => 'Solar', 'title' => 'Solar', 'phrase' => 'solar',
            'services' => ['Solar Panel Install', 'Solar + Battery', 'Battery Backup', 'Solar Repair', 'Panel Cleaning', 'EV Charger', 'Commercial Solar'],
            'budgets' => ['Under $100', '$100 - $200', '$200 - $300', '$300 - $500', '$500+'],
            'offer' => ['title' => 'Free Solar Savings Report', 'body' => 'See how much you could save with a custom design and savings estimate for your home.'],
            'copy' => [
                'badge' => 'Free Solar Quote',
                'pill' => 'Get Your Savings Report in 24 Hours',
                'subheadline' => 'Your free solar quote in 24 hours.',
                'intro' => 'Tell us about your home and get a free, no-obligation solar quote and savings estimate.',
                'form_intro' => 'Answer a few quick questions and we will design a system for your home and show you the savings - free, with no obligation.',
                'services_label' => 'What are you interested in?',
                'timeline_label' => 'When are you looking to go solar?',
                'timelines' => ['As soon as possible', 'Within 1-3 months', 'Within 3-6 months', 'Just researching'],
                'budget_label' => 'Average Monthly Electric Bill',
                'budget_hint' => 'This helps us size your system.',
                'message_label' => 'Tell us about your home',
                'message_placeholder' => 'Do you own the home? Roof type and age, shade, or anything else we should know.',
                'message_required' => false,
                'phone_hint' => "We'll only use this to contact you about your quote.",
                'cta' => 'Get My Free Solar Quote',
                'trust' => [['Custom Design', 'A system sized for your home'], ['Savings Estimate', 'See what you could save'], ['No Obligation', 'Zero pressure'], ['Fast Response', 'Quick turnaround on your quote']],
                'response' => 'Savings report within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Savings estimates depend on your usage, utility rates, roof and available incentives, and are not guaranteed.',
            ],
        ],
        'flooring' => [
            'label' => 'Flooring', 'noun' => 'Flooring',
            'services' => ['Hardwood', 'Luxury Vinyl Plank', 'Tile', 'Laminate', 'Carpet', 'Floor Refinishing', 'Stairs', 'Floor Repair'],
            'budgets' => ['Under $1,500', '$1,500 - $5,000', '$5,000 - $10,000', '$10,000 - $20,000', '$20,000+'],
            'offer' => ['title' => 'Free In-Home Measure', 'body' => 'We bring samples to your home, measure the space and leave you with a clear written quote.'],
        ],
        'pest_control' => [
            'label' => 'Pest Control', 'noun' => 'Pest Control', 'phrase' => 'pest control',
            'services' => ['General Pest Control', 'Termites', 'Rodents', 'Bed Bugs', 'Scorpions', 'Ants', 'Mosquitoes', 'Wildlife Removal'],
            'budgets' => ['Under $150', '$150 - $300', '$300 - $750', '$750 - $1,500', '$1,500+'],
            'offer' => ['title' => '$50 Off Your First Service', 'body' => 'New customers save $50 on their first treatment, any size home.'],
        ],
        'lawyers' => [
            'label' => 'Lawyers', 'noun' => 'Legal', 'group' => 'pro', 'title' => 'Attorneys', 'phrase' => 'legal',
            'services' => ['Personal Injury', 'Car Accident', 'Family & Divorce', 'Criminal Defense', 'DUI', 'Estate Planning', 'Immigration', "Workers' Compensation", 'Business Law'],
            'budgets' => [],
            'offer' => ['title' => 'Free Case Review', 'body' => 'Talk with an attorney about your situation at no cost and with no obligation.'],
            'copy' => [
                'badge' => 'Free Case Review',
                'pill' => 'Free, Confidential Case Review',
                'subheadline' => 'Your free case review starts here.',
                'intro' => 'Tell us what happened and an attorney will review your situation for free.',
                'form_intro' => 'Answer a few quick questions and we will get back to you to talk through your options - free and with no obligation.',
                'services_label' => 'What do you need help with?',
                'timeline_label' => 'When did this happen?',
                'timelines' => ['Within the last week', 'Within the last month', '1-6 months ago', 'More than 6 months ago', 'Planning ahead'],
                'budget_label' => null,
                'message_label' => 'Briefly describe your situation',
                'message_placeholder' => 'A few sentences is plenty. Please do not include confidential details yet - we will go over them with you privately.',
                'phone_hint' => 'We will only use this to contact you about your case.',
                'cta' => 'Get My Free Case Review',
                'trust' => [['Free Case Review', 'Talk through your options at no cost'], ['Straight Answers', 'Plain-English advice on where you stand'], ['No Obligation', 'You decide what happens next'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Free case review',
                'license_label' => 'Bar number',
                'consent' => true,
                'disclaimer' => 'Attorney advertising. Submitting this form does not create an attorney-client relationship. Please do not send confidential information until an attorney-client relationship has been established. Prior results do not guarantee a similar outcome.',
            ],
        ],
        'mortgage' => [
            'label' => 'Mortgage', 'noun' => 'Mortgage', 'group' => 'pro', 'title' => 'Mortgage Loans', 'phrase' => 'mortgage',
            'services' => ['Home Purchase', 'Refinance', 'Cash-Out Refinance', 'First-Time Buyer', 'FHA Loan', 'VA Loan', 'Jumbo Loan', 'HELOC'],
            'budgets' => ['Under $200,000', '$200,000 - $400,000', '$400,000 - $750,000', '$750,000 - $1,000,000', '$1,000,000+'],
            'offer' => ['title' => 'Free Rate Review', 'body' => 'See what you could qualify for today. A quick review with no obligation.'],
            'copy' => [
                'badge' => 'Free Rate Quote',
                'pill' => 'Get Your Rate Quote in 24 Hours',
                'subheadline' => 'Your free rate quote in 24 hours.',
                'intro' => 'Tell us what you are looking for and get a free, no-obligation rate quote.',
                'form_intro' => 'Answer a few quick questions and we will put together your options and rates - free, with no obligation.',
                'services_label' => 'What are you looking for?',
                'timeline_label' => 'When are you looking to buy or refinance?',
                'timelines' => ['As soon as possible', 'Within 1-3 months', 'Within 3-6 months', 'Just exploring options'],
                'budget_label' => 'Estimated Loan Amount',
                'budget_hint' => 'A rough figure is fine.',
                'message_label' => 'Anything else we should know?',
                'message_placeholder' => 'For example: the kind of home you want, your current rate, or questions you have. Please do not include your Social Security number or account numbers.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to contact you about your quote.',
                'cta' => 'Get My Free Rate Quote',
                'trust' => [['Free Rate Quote', 'See your options side by side'], ['No Obligation', 'Compare with zero pressure'], ['Clear Numbers', 'Rates and costs explained plainly'], ['Fast Response', 'Quick turnaround on your quote']],
                'response' => 'Rate quote within 24 hours',
                'license_label' => 'NMLS #',
                'license_required' => true,
                'consent' => true,
                'disclaimer' => 'Equal Housing Lender. This is not a commitment to lend. Rates, terms and programs are subject to credit approval and may change without notice.',
            ],
        ],
        'real_estate' => [
            'label' => 'Real Estate', 'noun' => 'Real Estate', 'group' => 'pro', 'title' => 'Real Estate', 'phrase' => 'real estate',
            'services' => ['Buying a Home', 'Selling a Home', 'Free Home Valuation', 'Buying & Selling', 'First-Time Buyer', 'Relocating', 'Investment Property'],
            'budgets' => ['Under $300,000', '$300,000 - $500,000', '$500,000 - $750,000', '$750,000 - $1,000,000', '$1,000,000+'],
            'offer' => ['title' => 'Free Home Valuation', 'body' => 'Find out what your home is worth in today\'s market, with a full report and no obligation.'],
            'copy' => [
                'badge' => 'Free Home Valuation',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Buying or selling? Let\'s talk.',
                'intro' => 'Tell us about your move and get a free, no-obligation consultation.',
                'form_intro' => 'Answer a few quick questions and we will reach out to talk through your plans - free, with no obligation.',
                'services_label' => 'How can we help?',
                'timeline_label' => 'When are you looking to buy or sell?',
                'timelines' => ['As soon as possible', 'Within 1-3 months', 'Within 3-6 months', '6+ months', 'Just exploring'],
                'budget_label' => 'Price Range',
                'budget_hint' => 'A rough range is fine.',
                'message_label' => 'Tell us about your plans',
                'message_placeholder' => 'Neighborhoods you like, the home you are selling, must-haves, anything that helps.',
                'phone_hint' => 'We will only use this to contact you about your move.',
                'cta' => 'Get My Free Consultation',
                'trust' => [['Local Expertise', 'Know the market before you move'], ['Free Home Valuation', 'See what your home is worth'], ['No Obligation', 'Talk it through with zero pressure'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Equal Housing Opportunity. Home values are estimates and are not appraisals.',
            ],
        ],
        'insurance' => [
            'label' => 'Insurance', 'noun' => 'Insurance', 'group' => 'pro', 'title' => 'Insurance', 'phrase' => 'insurance',
            'services' => ['Auto', 'Home', 'Renters', 'Life', 'Health', 'Business', 'Umbrella', 'Motorcycle'],
            'budgets' => [],
            'offer' => ['title' => 'Free Coverage Review', 'body' => 'We compare your current policy and show you where you could save.'],
            'copy' => [
                'badge' => 'Free Insurance Quote',
                'pill' => 'Get Your Quote in 24 Hours',
                'subheadline' => 'Your free insurance quote in 24 hours.',
                'intro' => 'Tell us what you need covered and get a free, no-obligation quote.',
                'form_intro' => 'Answer a few quick questions and we will shop your coverage and get back to you - free, with no obligation.',
                'services_label' => 'What coverage do you need?',
                'timeline_label' => 'When do you need coverage?',
                'timelines' => ['Right away', 'Within 30 days', 'Within 1-3 months', 'Just comparing'],
                'budget_label' => null,
                'message_label' => 'Anything else we should know?',
                'message_placeholder' => 'For example: what you have now, when it renews, or what you want covered. Please do not include policy or Social Security numbers.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to contact you about your quote.',
                'cta' => 'Get My Free Quote',
                'trust' => [['Free Quote', 'Compare your options'], ['Coverage Review', 'Make sure you are protected'], ['No Obligation', 'Zero pressure to switch'], ['Fast Response', 'Quick turnaround on your quote']],
                'response' => 'Quote within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Quotes are estimates. Coverage is subject to underwriting and the terms of the policy issued.',
            ],
        ],
        'plastic_surgery' => [
            'label' => 'Plastic Surgery', 'noun' => 'Cosmetic Surgery', 'group' => 'pro', 'title' => 'Plastic Surgery', 'phrase' => 'cosmetic surgery',
            'services' => ['Breast Augmentation', 'Breast Lift', 'Tummy Tuck', 'Liposuction', 'Mommy Makeover', 'Rhinoplasty', 'Facelift', 'Eyelid Surgery', 'BBL'],
            'budgets' => [],
            'offer' => ['title' => 'Complimentary Consultation', 'body' => 'Meet with our team, ask every question and get a personal plan, free.'],
            'copy' => [
                'badge' => 'Free Consultation',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Book your free consultation.',
                'intro' => 'Tell us what you are interested in and book a free, no-obligation consultation.',
                'form_intro' => 'Tell us which treatments interest you and we will reach out to set up your free consultation.',
                'services_label' => 'What are you interested in?',
                'timeline_label' => 'When are you hoping to have surgery?',
                'timelines' => ['As soon as possible', 'Within 1-3 months', 'Within 3-6 months', 'Just researching'],
                'budget_label' => null,
                'message_label' => 'Any questions for us?',
                'message_placeholder' => 'Questions about the procedure, recovery, financing or scheduling. Please do not include medical history here - we will go over it privately at your consultation.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to schedule your consultation.',
                'cta' => 'Book My Free Consultation',
                'trust' => [['Free Consultation', 'A personal plan made for you'], ['Ask Anything', 'Every question answered'], ['No Obligation', 'Decide in your own time'], ['Financing Options', 'Ask us about payment plans']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Individual results vary. Please do not submit medical information through this form.',
            ],
        ],
        'med_spa' => [
            'label' => 'Med Spa', 'noun' => 'Med Spa', 'group' => 'pro', 'title' => 'Med Spa', 'phrase' => 'med spa',
            'services' => ['Botox & Dysport', 'Dermal Fillers', 'Laser Hair Removal', 'Microneedling', 'Chemical Peels', 'HydraFacial', 'Body Contouring', 'Medical Weight Loss', 'IV Therapy'],
            'budgets' => [],
            'offer' => ['title' => 'First Visit Special', 'body' => 'New clients get a complimentary skin consultation and a special price on their first treatment.'],
            'copy' => [
                'badge' => 'Free Consultation',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Book your free consultation.',
                'intro' => 'Tell us what you are interested in and book a free, no-obligation consultation.',
                'form_intro' => 'Tell us which treatments interest you and we will reach out to book your free consultation.',
                'services_label' => 'What are you interested in?',
                'timeline_label' => 'When would you like to come in?',
                'timelines' => ['As soon as possible', 'Within 2 weeks', 'Within a month', 'Just researching'],
                'budget_label' => null,
                'message_label' => 'Any questions for us?',
                'message_placeholder' => 'Questions about treatments, pricing, memberships or scheduling. Please do not include medical history here - we will go over it privately at your consultation.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to book your consultation.',
                'cta' => 'Book My Free Consultation',
                'trust' => [['Free Consultation', 'A treatment plan made for you'], ['Licensed Providers', 'Treatments by trained professionals'], ['No Obligation', 'Decide in your own time'], ['Memberships & Packages', 'Ask us about saving more']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Individual results vary. Please do not submit medical information through this form.',
            ],
        ],
        'dental' => [
            'label' => 'Dental', 'noun' => 'Dental', 'group' => 'pro', 'title' => 'Dentist', 'phrase' => 'dental',
            'services' => ['Dental Implants', 'Invisalign', 'Veneers', 'Teeth Whitening', 'Cleanings & Checkups', 'Crowns', 'Dentures', 'Emergency Dental'],
            'budgets' => [],
            'offer' => ['title' => 'New Patient Special', 'body' => 'Exam, X-rays and cleaning for new patients at a special price.'],
            'copy' => [
                'badge' => 'New Patient Consultation',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Book your visit today.',
                'intro' => 'Tell us what you need and we will get you scheduled.',
                'form_intro' => 'Tell us what you are looking for and we will reach out to schedule your visit.',
                'services_label' => 'What are you interested in?',
                'timeline_label' => 'When would you like to come in?',
                'timelines' => ['As soon as possible', 'Within 2 weeks', 'Within a month', 'Just researching'],
                'budget_label' => null,
                'message_label' => 'Any questions for us?',
                'message_placeholder' => 'Questions about treatment, insurance or scheduling. Please do not include medical history here.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to schedule your visit.',
                'cta' => 'Request My Appointment',
                'trust' => [['Gentle Care', 'Comfort-first dentistry'], ['Clear Pricing', 'Know the cost before treatment'], ['No Obligation', 'Ask questions first'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'Individual results vary. Please do not submit medical information through this form.',
            ],
        ],
        'accounting' => [
            'label' => 'Accounting', 'noun' => 'Accounting', 'group' => 'pro', 'title' => 'Accounting', 'phrase' => 'accounting and bookkeeping',
            'services' => ['Bookkeeping', 'Payroll', 'Monthly Accounting', 'Financial Statements', 'QuickBooks Setup & Cleanup', 'Business Formation', 'CFO Services'],
            'budgets' => [],
            'offer' => ['title' => 'Free Books Review', 'body' => 'We look over your books and show you where we can save you time and money, free.'],
            'copy' => [
                'badge' => 'Free Consultation',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Your free consultation starts here.',
                'intro' => 'Tell us about your business and get a free, no-obligation consultation.',
                'form_intro' => 'Answer a few quick questions and we will reach out to talk through how we can help - free, with no obligation.',
                'services_label' => 'What do you need help with?',
                'timeline_label' => 'When do you need help?',
                'timelines' => ['Right away', 'Within a month', 'Within 1-3 months', 'Just exploring'],
                'budget_label' => null,
                'message_label' => 'Tell us a bit about your business',
                'message_placeholder' => 'What kind of business, roughly how many transactions or employees, and what you need done. Please do not include account numbers.',
                'phone_hint' => 'We will only use this to contact you about your request.',
                'cta' => 'Get My Free Consultation',
                'trust' => [['Free Consultation', 'Talk it through at no cost'], ['Clear Pricing', 'Know the cost up front'], ['No Obligation', 'Zero pressure'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'CPA license #',
                'consent' => true,
                'disclaimer' => 'This form is for information only and is not tax, legal or financial advice.',
            ],
        ],
        'tax' => [
            'label' => 'Tax Experts', 'noun' => 'Tax', 'group' => 'pro', 'title' => 'Tax Experts', 'phrase' => 'tax',
            'services' => ['Personal Tax Return', 'Business Tax Return', 'Back Taxes', 'IRS Letters & Audits', 'Tax Debt Relief', 'Tax Planning', 'Amended Returns', 'Payroll Tax Problems'],
            'budgets' => [],
            'offer' => ['title' => 'Free Tax Review', 'body' => 'We review your situation and tell you your options, free and with no obligation.'],
            'copy' => [
                'badge' => 'Free Tax Review',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Your free tax review starts here.',
                'intro' => 'Tell us about your tax situation and get a free, no-obligation review.',
                'form_intro' => 'Answer a few quick questions and we will reach out to talk through your options - free, with no obligation.',
                'services_label' => 'What do you need help with?',
                'timeline_label' => 'How soon do you need help?',
                'timelines' => ['Right away - I have a deadline or IRS letter', 'Within a month', 'Before tax season', 'Just exploring'],
                'budget_label' => null,
                'message_label' => 'Tell us a bit about your situation',
                'message_placeholder' => 'Personal or business, which tax years, and any letters you have received. Please do not include Social Security or account numbers.',
                'phone_hint' => 'We will only use this to contact you about your request.',
                'cta' => 'Get My Free Tax Review',
                'trust' => [['Free Tax Review', 'Know your options at no cost'], ['Straight Answers', 'Plain-English advice'], ['No Obligation', 'Zero pressure'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License / PTIN',
                'consent' => true,
                'disclaimer' => 'This form is for information only and is not tax, legal or financial advice. Results depend on your individual situation.',
            ],
        ],
        'finance' => [
            'label' => 'Finance', 'noun' => 'Financial Planning', 'group' => 'pro', 'title' => 'Financial Advisors', 'phrase' => 'financial planning',
            'services' => ['Retirement Planning', 'Investment Management', 'Financial Planning', '401(k) Rollover', 'Estate & Legacy Planning', 'College Savings', 'Business Owner Planning', 'Insurance & Annuities'],
            'budgets' => ['Under $100,000', '$100,000 - $250,000', '$250,000 - $500,000', '$500,000 - $1,000,000', '$1,000,000+'],
            'offer' => ['title' => 'Free Retirement Review', 'body' => 'A no-cost look at where you stand and what it would take to retire on your terms.'],
            'copy' => [
                'badge' => 'Free Financial Review',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Your free financial review starts here.',
                'intro' => 'Tell us about your goals and get a free, no-obligation financial review.',
                'form_intro' => 'Answer a few quick questions and we will reach out to talk through your goals and options - free, with no obligation.',
                'services_label' => 'What would you like help with?',
                'timeline_label' => 'When would you like to get started?',
                'timelines' => ['As soon as possible', 'Within 1-3 months', 'Within 3-6 months', 'Just exploring'],
                'budget_label' => 'Investable Assets',
                'budget_hint' => 'A rough range is fine. This helps us match you with the right advisor.',
                'message_label' => 'Tell us about your goals',
                'message_placeholder' => 'For example: when you hope to retire, what you are saving for, or questions you have. Please do not include account or Social Security numbers.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to contact you about your review.',
                'cta' => 'Get My Free Financial Review',
                'trust' => [['Free Review', 'See where you stand today'], ['A Plan for You', 'Advice built around your goals'], ['No Obligation', 'Zero pressure'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'CRD #',
                'consent' => true,
                'disclaimer' => 'Investing involves risk, including possible loss of principal. This form is for information only and is not investment, tax or legal advice.',
            ],
        ],
        'medical' => [
            'label' => 'Medical', 'noun' => 'Medical', 'group' => 'pro', 'title' => 'Medical Care', 'phrase' => 'medical',
            'services' => ['Primary Care', 'Urgent Care', 'Physical Therapy', 'Chiropractic', 'Dermatology', 'Weight Loss', "Men's Health", "Women's Health", 'Telehealth'],
            'budgets' => [],
            'offer' => ['title' => 'New Patient Welcome', 'body' => 'New patients get a same-week appointment and a welcome visit with our team.'],
            'copy' => [
                'badge' => 'New Patient Appointment',
                'pill' => 'Hear Back Within 24 Hours',
                'subheadline' => 'Book your visit today.',
                'intro' => 'Tell us what you need and we will get you scheduled.',
                'form_intro' => 'Tell us what kind of visit you need and we will reach out to schedule your appointment.',
                'services_label' => 'What kind of care do you need?',
                'timeline_label' => 'When would you like to be seen?',
                'timelines' => ['As soon as possible', 'This week', 'Within a month', 'Just researching'],
                'budget_label' => null,
                'message_label' => 'Anything else we should know?',
                'message_placeholder' => 'Questions about insurance, location or scheduling. Please do not include medical history or symptoms here - we will go over them privately.',
                'message_required' => false,
                'phone_hint' => 'We will only use this to schedule your visit.',
                'cta' => 'Request My Appointment',
                'trust' => [['Caring Providers', 'Unhurried, attentive care'], ['Easy Scheduling', 'Appointments that fit your week'], ['Insurance Friendly', 'Ask us about your plan'], ['Fast Response', 'We get back to you quickly']],
                'response' => 'Reply within 24 hours',
                'license_label' => 'License #',
                'consent' => true,
                'disclaimer' => 'This form is not for emergencies - if you have a medical emergency, call 911. Please do not submit medical information through this form.',
            ],
        ],
        'other' => [
            'label' => 'Other', 'noun' => 'Service', 'group' => 'other',
            'services' => ['Installation', 'Repair', 'Maintenance', 'Consultation'],
            'budgets' => ['Under $500', '$500 - $2,500', '$2,500 - $10,000', '$10,000+'],
            'offer' => ['title' => 'Free Estimate', 'body' => 'Tell us about your project and get a free, no-obligation estimate.'],
        ],
    ];

    /** Group headings for the builder's trade picker. */
    public const GROUPS = ['home' => 'Home services', 'pro' => 'Professional services', 'other' => 'Something else'];

    /**
     * How the lead form reads for contractors. Professional trades replace
     * any of these in their 'copy'. {noun} is filled in from the trade.
     */
    public const DEFAULT_COPY = [
        'badge' => 'Free {noun} Quote',
        'pill' => 'Get Your Quote in 24 Hours',
        'subheadline' => 'Your free quote in 24 hours.',
        'intro' => 'Tell us about your project and get a free, no-obligation quote.',
        'form_intro' => "Fill out this quick form and we'll provide a detailed quote and design consultation - completely free, no strings attached.",
        'services_label' => 'Choose Your Services',
        'timeline_label' => 'When are you looking to start?',
        'timelines' => null,
        'budget_label' => 'Estimated Budget Range',
        'budget_hint' => 'This helps us provide accurate recommendations.',
        'message_label' => 'Tell us about your project',
        'message_placeholder' => 'What is your vision? Any specific features you want? Current pain points with your space? The more details, the better we can help!',
        'message_required' => true,
        'phone_hint' => "We'll only use this to contact you about your quote.",
        'cta' => 'Get My Free Quote & Consultation',
        'trust' => [['Free Design Consultation', 'Expert advice tailored to your vision'], ['Detailed Quote', 'Transparent pricing with no hidden fees'], ['No Obligation', 'Get expert insights with zero pressure'], ['Fast Response', 'Quick turnaround on your quote']],
        'response' => 'Free quote within 24 hours',
        'license_label' => null,
        'license_required' => false,
        'consent' => false,
        'disclaimer' => null,
    ];

    /** Old keys still stored on some pages, and what they are called now. */
    private const ALIASES = ['cosmetic' => 'plastic_surgery'];

    /** The current key for a stored one ("cosmetic" -> "plastic_surgery"). */
    public static function key(?string $key): string
    {
        $key = (string) $key;
        return self::ALIASES[$key] ?? $key;
    }

    public static function exists(string $key): bool
    {
        return isset(self::ALL[$key]);
    }

    /** @return array<string,mixed> */
    public static function get(string $key): array
    {
        return self::ALL[self::ALIASES[$key] ?? $key] ?? self::ALL['other'];
    }

    /**
     * The form wording for a trade: the defaults with the trade's own copy on
     * top. An unknown or empty key (pages made before trades were stored)
     * reads as a contractor form, which is what those pages always were.
     *
     * @return array<string,mixed>
     */
    public static function copy(?string $key): array
    {
        $trade = self::get((string) $key);
        $copy = array_replace(self::DEFAULT_COPY, $trade['copy'] ?? []);
        $copy['badge'] = str_replace('{noun}', (string) $trade['noun'], (string) $copy['badge']);
        return $copy;
    }

    /** For the builder: each trade with its copy resolved. @return array<string,array<string,mixed>> */
    public static function forBuilder(): array
    {
        $out = [];
        foreach (self::ALL as $key => $trade) {
            $out[$key] = $trade + ['group' => 'home'];
            $out[$key]['copy'] = self::copy($key);
        }
        return $out;
    }

    /**
     * The contact consent a lead agrees to on industries that call and text
     * leads back (TCPA). Stored word for word with the lead.
     */
    public static function consentText(string $business): string
    {
        return 'I agree that ' . $business . ' may contact me at the phone number and email I provided, including by calls '
            . 'and text messages sent using automated technology or prerecorded voice, about my request. Consent is not a '
            . 'condition of any purchase. Message and data rates may apply; reply STOP to opt out.';
    }

    /** "provides {phrase} services in Phoenix" @param array<string,mixed> $trade */
    public static function phrase(array $trade): string
    {
        return (string) ($trade['phrase'] ?? mb_strtolower((string) $trade['label']));
    }
}
