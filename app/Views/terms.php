<?php
use App\Support\Config;
use App\Support\Plans;
use App\Support\View;

$company = (string) Config::get('legal.company', 'LeadCrazy');
$state = (string) Config::get('legal.state', 'Arizona');
$email = (string) Config::get('support_email', 'support@leadcrazy.com');
$updated = 'October 26, 2026';
$price = static fn (string $plan): string => '$' . Plans::price($plan) . '/month';
?>
<section class="section">
  <div class="container prose legal">
    <span class="eyebrow">Legal</span>
    <h1>Terms of Service</h1>
    <p class="muted">Last updated <?= View::e($updated) ?></p>

    <p>These terms are an agreement between you and <?= View::e($company) ?> (&ldquo;LeadCrazy&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;) for the use of leadcrazy.com, the lead pages and forms we host, the embed code and the members area (together, the &ldquo;Service&rdquo;). By creating an account or using the Service you agree to these terms and to our <a href="/privacy">Privacy Policy</a>. If you use LeadCrazy for a business, you agree on behalf of that business.</p>

    <nav class="legal__toc" aria-label="On this page">
      <a href="#account">Your account</a>
      <a href="#plans">Plans and billing</a>
      <a href="#pages">Your page and approval</a>
      <a href="#content">Your content</a>
      <a href="#leads">Leads and contacting people</a>
      <a href="#regulated">Licensed and regulated businesses</a>
      <a href="#use">Acceptable use</a>
      <a href="#embed">Embed code</a>
      <a href="#ours">Our property</a>
      <a href="#ending">Suspension and cancellation</a>
      <a href="#disclaimers">Disclaimers</a>
      <a href="#liability">Limitation of liability</a>
      <a href="#indemnity">Indemnity</a>
      <a href="#law">Governing law</a>
      <a href="#changes">Changes</a>
      <a href="#contact">Contact</a>
    </nav>

    <h2 id="account">1. Your account</h2>
    <ul>
      <li>You must be at least 18 and able to enter a contract.</li>
      <li>Give accurate information and keep it up to date. One account per business; we may merge or decline duplicate profiles.</li>
      <li>Keep your password private. You are responsible for activity on your account. Tell us right away if you think someone else has accessed it.</li>
    </ul>

    <h2 id="plans">2. Plans and billing</h2>
    <ul>
      <li><strong><?= View::e(Plans::name(Plans::FREE)) ?></strong> (<?= View::e($price(Plans::FREE)) ?>): a hosted lead page with up to <?= Plans::FREE_MONTHLY_LEADS ?> leads you can view each month. Leads above that are saved and unlock when you upgrade. Free pages show LeadCrazy branding and list up to <?= Plans::FREE_OFFERS ?> offer, <?= Plans::FREE_CITIES ?> cities and <?= Plans::FREE_ZIPS ?> zip codes.</li>
      <li><strong><?= View::e(Plans::name(Plans::PRO)) ?></strong> (<?= View::e($price(Plans::PRO)) ?>): the full hosted page, unlimited leads, and a listing on MonsterList.</li>
      <li><strong><?= View::e(Plans::name(Plans::PREMIUM)) ?></strong> (<?= View::e($price(Plans::PREMIUM)) ?>): everything in <?= View::e(Plans::name(Plans::PRO)) ?>, plus embed code for your own website.</li>
      <li>Paid plans are billed monthly in advance through Stripe and <strong>renew automatically</strong> until cancelled. By choosing a paid plan you authorize us to charge your payment method each month.</li>
      <li>You can cancel any time from your billing page. Your plan stays active until the end of the period you paid for, then moves to <?= View::e(Plans::name(Plans::FREE)) ?>. Payments are non-refundable except where the law requires otherwise.</li>
      <li>If a payment fails, we may move your page to <?= View::e(Plans::name(Plans::FREE)) ?> until it is resolved. Prices may change; we will tell you at least 30 days before a change affects your plan.</li>
      <li>Prices do not include taxes, which may be added where required.</li>
    </ul>

    <h2 id="pages">3. Your page and approval</h2>
    <p>You build your page with our builder, or our team builds it from what you give us. Every new page is reviewed before it goes live, and we may edit wording or layout to keep pages clear and working. We may decline or remove a page that does not meet these terms. Your page address (leadcrazy.com/your-business) is provided as part of the Service and is not yours to keep if your account closes.</p>

    <h2 id="content">4. Your content</h2>
    <ul>
      <li>You own the content you give us: text, photos, logos, offers, reviews and so on. You give us permission to host, copy, display and adapt it to run the Service, to show it on your page and in embeds, and, for <?= View::e(Plans::name(Plans::PRO)) ?> and <?= View::e(Plans::name(Plans::PREMIUM)) ?> pages, to list it on MonsterList.</li>
      <li>You are responsible for your content being true, lawful and yours to use. That includes prices, offers, license numbers, claims and credentials.</li>
      <li>Reviews and testimonials must come from real customers and must not be edited to change their meaning. You must honor offers as stated on your page.</li>
    </ul>

    <h2 id="leads">5. Leads and contacting people</h2>
    <ul>
      <li>Leads are requests from people who chose to contact your business. Use them only to respond to that request, and honor every request to stop contacting someone promptly, including STOP replies to texts.</li>
      <li>You are responsible for following the laws that apply to how you contact people, including the Telephone Consumer Protection Act (TCPA), the CAN-SPAM Act, Do Not Call rules and state privacy and telemarketing laws.</li>
      <li>Do not sell, rent or share leads with anyone else unless the person clearly agreed to it and the law allows it.</li>
      <li>Keep lead information secure and delete it when you no longer need it.</li>
      <li>We do not promise any number, quality or value of leads, or that any lead will become a customer.</li>
    </ul>

    <h2 id="regulated">6. Licensed and regulated businesses</h2>
    <p>Some industries have extra rules, such as law, mortgage and lending, real estate, insurance, financial advice, tax and accounting, and medical, dental and cosmetic services. If your business is one of them, you are responsible for:</p>
    <ul>
      <li>holding the licenses and registrations your work requires and showing the ones the law requires on your page (for example, an NMLS number);</li>
      <li>making sure your page, offers and disclaimers meet your industry's advertising rules;</li>
      <li>not asking for, and not relying on the form to collect, protected health information, Social Security numbers, account numbers or other sensitive data. LeadCrazy is not designed to store that kind of information, and we do not sign business associate agreements.</li>
    </ul>
    <p>The industry wording, consent checkboxes and disclaimers we provide are a helpful starting point, not legal advice. Have your own advisor review them.</p>

    <h2 id="use">7. Acceptable use</h2>
    <p>Do not use LeadCrazy to:</p>
    <ul>
      <li>offer illegal products or services, or services you are not licensed to provide;</li>
      <li>make false or misleading claims, post fake reviews, or pretend to be another business or person;</li>
      <li>collect information for anyone other than your own business, or resell leads;</li>
      <li>upload content you do not have the right to use, or that is hateful, harassing or adult;</li>
      <li>send spam, submit fake leads, or try to get around plan limits;</li>
      <li>attack, overload, probe or reverse engineer the Service, or access accounts or data that are not yours.</li>
    </ul>

    <h2 id="embed">8. Embed code</h2>
    <p><?= View::e(Plans::name(Plans::PREMIUM)) ?> members may place our embed code on websites they own or control. Do not change the code to remove required notices or to get around plan limits. If your plan ends, the embed stops showing your form.</p>

    <h2 id="ours">9. Our property</h2>
    <p>The Service, including the Million Dollar Lead Form design, builder, templates, software and the LeadCrazy name and logo, belongs to LeadCrazy and is protected by law. These terms give you the right to use the Service while your account is active; they do not give you ownership of it. If you send us suggestions, we may use them without owing you anything.</p>

    <h2 id="ending">10. Suspension and cancellation</h2>
    <p>You can stop using LeadCrazy and ask us to delete your account at any time. We may pause or remove a page, or suspend or close an account, if you break these terms, if we are required to by law, or to protect people or the Service. Where reasonable, we will tell you first and give you a chance to fix the problem. When an account is deleted, its page and leads are deleted too, so export anything you need first. Sections 4 to 6 and 9 to 14 continue after an account closes.</p>

    <h2 id="disclaimers">11. Disclaimers</h2>
    <p>The Service is provided &ldquo;as is&rdquo; and &ldquo;as available&rdquo;. We work to keep it running and secure, but we do not promise it will be uninterrupted or error-free, that every lead or email will be delivered, or that it will produce any particular result for your business. To the fullest extent the law allows, we disclaim all warranties, including merchantability, fitness for a particular purpose and non-infringement. Lead calculators and estimates on our site are illustrations, not promises.</p>

    <h2 id="liability">12. Limitation of liability</h2>
    <p>To the fullest extent the law allows, LeadCrazy will not be liable for any indirect, incidental, special, consequential or punitive damages, or for lost profits, revenue, data or business opportunities. Our total liability for any claim relating to the Service is limited to the amount you paid us in the 12 months before the claim, or $100 if you have not paid us anything. Some places do not allow these limits, so they may not all apply to you.</p>

    <h2 id="indemnity">13. Indemnity</h2>
    <p>You agree to defend and hold LeadCrazy harmless from claims, losses and costs, including reasonable legal fees, that come from your content, how you contact or use leads, your services to your customers, or your breach of these terms or the law.</p>

    <h2 id="law">14. Governing law and disputes</h2>
    <p>These terms are governed by the laws of the State of <?= View::e($state) ?> and the United States, without regard to conflict-of-law rules. Before filing a claim, please contact us so we can try to resolve it informally. Any dispute that is not resolved will be heard in the state or federal courts located in <?= View::e($state) ?>, and you and we agree to those courts' jurisdiction. Nothing here stops either side from going to small claims court.</p>

    <h2 id="changes">15. Changes to these terms</h2>
    <p>We may update these terms. We will change the date at the top, and for significant changes we will tell members by email or in the members area at least 14 days before they apply. If you keep using the Service after a change takes effect, you accept the updated terms.</p>

    <h2 id="contact">16. Contact</h2>
    <p>Questions about these terms: <a href="mailto:<?= View::e($email) ?>"><?= View::e($email) ?></a></p>
  </div>
</section>
