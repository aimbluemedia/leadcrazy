<?php
use App\Support\Config;
use App\Support\Plans;
use App\Support\View;

$company = (string) Config::get('legal.company', 'LeadCrazy');
$address = trim((string) Config::get('legal.address', ''));
$email = (string) Config::get('support_email', 'support@leadcrazy.com');
$updated = 'October 26, 2026';
?>
<section class="section">
  <div class="container prose legal">
    <span class="eyebrow">Legal</span>
    <h1>Privacy Policy</h1>
    <p class="muted">Last updated <?= View::e($updated) ?></p>

    <p>This policy explains what information <?= View::e($company) ?> (&ldquo;LeadCrazy&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;) collects, how we use it, and the choices you have. It covers leadcrazy.com, the lead pages we host for businesses, the lead forms businesses embed on their own websites, and the members area.</p>

    <nav class="legal__toc" aria-label="On this page">
      <a href="#who">Who this covers</a>
      <a href="#businesses">Businesses using LeadCrazy</a>
      <a href="#visitors">People who fill in a lead form</a>
      <a href="#use">How we use information</a>
      <a href="#share">Who we share it with</a>
      <a href="#cookies">Cookies</a>
      <a href="#keep">How long we keep it</a>
      <a href="#security">Security</a>
      <a href="#rights">Your choices and rights</a>
      <a href="#children">Children</a>
      <a href="#changes">Changes</a>
      <a href="#contact">Contact us</a>
    </nav>

    <h2 id="who">Who this covers</h2>
    <p>LeadCrazy is used by two groups of people:</p>
    <ul>
      <li><strong>Members</strong> &mdash; businesses that sign up to get a lead page and lead form.</li>
      <li><strong>Visitors</strong> &mdash; people who visit a member's page and send a request through its form.</li>
    </ul>
    <p>When a visitor sends a request, the information goes to the member business named on the form. That business decides how to follow up and is responsible for how it uses the information. We store and deliver it on the business's behalf.</p>

    <h2 id="businesses">Information from businesses using LeadCrazy</h2>
    <ul>
      <li><strong>Account details:</strong> your name, email address, a securely hashed password, and the business name.</li>
      <li><strong>Page content:</strong> what you give us to build your page, such as your phone number, city, website, services, service area, offers, photos, logo, reviews, license numbers and descriptions.</li>
      <li><strong>Billing:</strong> paid plans are billed by Stripe. Stripe collects your card details; we never see or store your full card number. We keep the Stripe customer and subscription references and the status of your plan.</li>
      <li><strong>Activity:</strong> sign-in times, sign-in attempts and the IP address they came from, password reset requests, change requests and a record of important account changes, so we can keep accounts secure and resolve problems.</li>
    </ul>

    <h2 id="visitors">Information from people who fill in a lead form</h2>
    <p>When you request a quote, consultation or appointment, we collect what you enter, which can include:</p>
    <ul>
      <li>your name, email address, phone number, city and zip code;</li>
      <li>the services you are interested in, your timing, budget or price range, the offer you chose, and your message;</li>
      <li>whether you agreed to be contacted by calls and texts, the exact wording you agreed to and when.</li>
    </ul>
    <p>We also record the IP address, browser type and referring page of each request to stop spam and abuse, and the page or website the request came from.</p>
    <p><strong>Please do not send sensitive information through a lead form</strong>, such as medical history, Social Security numbers, account numbers or confidential legal details. Forms are for getting in touch; the business will discuss details with you directly.</p>

    <h2 id="use">How we use information</h2>
    <ul>
      <li>To deliver a visitor's request to the business they chose, in that business's dashboard and by email.</li>
      <li>To build, host and show members' pages, and to run the members area.</li>
      <li>To bill paid plans and manage subscriptions.</li>
      <li>To send service emails, such as new-lead alerts, page approvals and password resets.</li>
      <li>To prevent spam, fraud and abuse, and to keep the service secure.</li>
      <li>To fix problems and improve LeadCrazy.</li>
    </ul>
    <p><strong>We do not sell personal information, and we do not use visitors' details to market to them ourselves.</strong> We do not share lead details with any business other than the one named on the form.</p>

    <h2 id="share">Who we share it with</h2>
    <ul>
      <li><strong>The business you contacted</strong> receives your lead details.</li>
      <li><strong>Service providers</strong> that help us run LeadCrazy, only as needed to provide their service: our web hosting provider; our email provider, which delivers notification emails; Stripe, for payments; and Cloudflare Turnstile, which helps tell people from bots on forms. Business pages load fonts from Google Fonts, which receives the visitor's IP address when the fonts load.</li>
      <li><strong>MonsterList:</strong> public page details of <?= View::e(Plans::name(Plans::PRO)) ?> and <?= View::e(Plans::name(Plans::PREMIUM)) ?> members, such as business name, services, area and page address, are listed in the MonsterList directory. Leads are never shared with MonsterList.</li>
      <li><strong>Legal reasons:</strong> when the law requires it, or to protect the rights, property or safety of our users, the public or LeadCrazy.</li>
      <li><strong>Business transfers:</strong> if LeadCrazy is sold or merged, information may transfer to the new owner under this policy.</li>
    </ul>

    <h2 id="cookies">Cookies</h2>
    <p>The members area and the superadmin area use one essential cookie to keep you signed in and to protect forms. Our public pages, business pages and lead forms set no cookies of their own and use no advertising or tracking cookies. Cloudflare Turnstile may use its own technology to check that a form is being filled in by a person.</p>

    <h2 id="keep">How long we keep it</h2>
    <ul>
      <li>Account and page information is kept while the account is open.</li>
      <li>Leads are kept in the business's dashboard while its account is open, so the business can follow up.</li>
      <li>When an account is deleted, its page, photos and leads are deleted with it. Copies in backups are removed as those backups are replaced.</li>
      <li>Security records, such as sign-in attempts and rate limits, are kept only as long as needed for security.</li>
    </ul>

    <h2 id="security">Security</h2>
    <p>Passwords are stored hashed and never in plain text. Sites are served over HTTPS, and access to the members and superadmin areas is protected by sign-in and limits on repeated attempts. No system is perfectly secure, but we work to protect your information and to limit who can see it.</p>

    <h2 id="rights">Your choices and rights</h2>
    <ul>
      <li><strong>Members</strong> can update their details from the members area and ask us to delete their account at any time.</li>
      <li><strong>Visitors</strong> who want a business to stop contacting them should tell that business; you can also reply STOP to text messages. To have your lead details deleted, contact the business, or email us with the business name and the email you used and we will help.</li>
      <li>Depending on where you live, such as California or the EU, you may have the right to know what we hold about you, to get a copy, to correct it, to delete it, or to object to how it is used. Email us and we will respond within the time the law allows. We will not treat you differently for using these rights.</li>
    </ul>

    <h2 id="children">Children</h2>
    <p>LeadCrazy is meant for adults and businesses. We do not knowingly collect information from children under 13. If you think a child has sent us information, contact us and we will delete it.</p>

    <h2 id="changes">Changes to this policy</h2>
    <p>We may update this policy from time to time. We will change the date at the top, and for significant changes we will let members know by email or in the members area.</p>

    <h2 id="contact">Contact us</h2>
    <p>Questions or requests about your information: <a href="mailto:<?= View::e($email) ?>"><?= View::e($email) ?></a><?= $address !== '' ? '<br>' . View::e($company) . ', ' . View::e($address) : '' ?></p>
  </div>
</section>
