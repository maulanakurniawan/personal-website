<x-layouts.public
    meta-title="Privacy Policy · Maulana Kurniawan"
    meta-description="Privacy policy for Maulana Kurniawan personal website, including public pages, technical articles, analytics, and contact form handling."
>
    <section class="mx-auto max-w-4xl space-y-5">
        <h1 class="text-3xl font-bold md:text-4xl">Privacy Policy</h1>
        <p class="text-sm text-base-content/70">Effective date: {{ now()->format('F j, Y') }}</p>

        <p class="text-sm text-base-content/75">
            This website provides public profile content, technical articles, and a contact form. I collect only the
            information needed to operate the site and respond to inquiries.
        </p>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Information collected</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Contact form data: name, email, subject, and message.</li>
                <li>Basic analytics and technical logs for performance and security monitoring.</li>
                <li>Standard request metadata handled by the hosting environment.</li>
            </ul>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">How information is used</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>To respond to inquiries and project discussions.</li>
                <li>To maintain reliability, security, and debugging visibility.</li>
                <li>To understand overall site usage trends and improve content quality.</li>
            </ul>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Data sharing</h2>
            <p class="text-sm text-base-content/75">
                Data is not sold. Limited data may be processed by infrastructure providers required to operate this
                site (for example hosting, email delivery, and analytics tools).
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">External links</h2>
            <p class="text-sm text-base-content/75">
                Articles may link to external websites. I am not responsible for third-party privacy practices.
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Contact</h2>
            <p class="text-sm text-base-content/75">
                For privacy questions, please use the
                <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.
            </p>
        </section>
    </section>
</x-layouts.public>
