<?php
/**
 * Vitrine Publique Copobimat par Camertech
 * Optimisée SEO & Expérience Utilisateur
 */
ob_start();
?>

<!-- Hero Section -->
<section id="home" class="position-relative pt-5 pb-5 overflow-hidden">
    <div class="container pt-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3 animate-fade-in">
                    🚀 Par Camertech - Solution 2024
                </div>
                <h1 class="display-3 fw-extra-bold mb-4" style="line-height: 1.1;">
                    <?= __('hero_title') ?>
                </h1>
                <p class="lead text-secondary mb-5 fs-4">
                    <?= __('hero_subtitle') ?>
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/login" class="btn btn-primary-gradient btn-lg px-5 py-3">Accéder à l'Espace</a>
                    <a href="https://wa.me/<?= __('whatsapp_number') ?>" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold shadow-lg hover-scale">
                        <i class="bi bi-whatsapp me-2"></i> <?= __('whatsapp_demo') ?>
                    </a>
                </div>
                <div class="mt-5 d-flex align-items-center gap-4">
                    <div class="d-flex -space-x-3">
                        <img src="https://i.pravatar.cc/100?u=1" class="rounded-circle border border-white" style="width: 40px; margin-right: -15px;">
                        <img src="https://i.pravatar.cc/100?u=2" class="rounded-circle border border-white" style="width: 40px; margin-right: -15px;">
                        <img src="https://i.pravatar.cc/100?u=3" class="rounded-circle border border-white" style="width: 40px;">
                    </div>
                    <span class="small text-secondary fw-medium">+100 établissements font confiance à Camertech</span>
                </div>
            </div>
            <div class="col-lg-6 position-relative">
                <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary opacity-5 rounded-circle" style="filter: blur(80px);"></div>
                <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" alt="Logiciel gestion scolaire Cameroun" class="img-fluid rounded-5 shadow-2xl position-relative animate-float" style="z-index: 2;">
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="bg-light">
    <div class="container">
        <div class="text-center mb-5 pb-3">
            <h2 class="display-5 fw-bold mb-3 text-dark"><?= __('services') ?></h2>
            <p class="text-secondary fs-5 mx-auto" style="max-width: 700px;">
                Camertech propose une suite d'outils intelligents pour transformer votre quotidien académique.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card p-5 h-100">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 20px; font-size: 2rem;">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <h3 class="fw-bold h4 mb-3"><?= __('service_management_title') ?></h3>
                    <p class="text-secondary"><?= __('service_management_desc') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-5 h-100">
                    <div class="icon-box bg-success bg-opacity-10 text-success mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 20px; font-size: 2rem;">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <h3 class="fw-bold h4 mb-3"><?= __('service_marks_title') ?></h3>
                    <p class="text-secondary"><?= __('service_marks_desc') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-5 h-100">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 20px; font-size: 2rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="fw-bold h4 mb-3"><?= __('service_discipline_title') ?></h3>
                    <p class="text-secondary"><?= __('service_discipline_desc') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" alt="Equipe Camertech" class="img-fluid rounded-5 shadow-lg">
            </div>
            <div class="col-lg-6 order-1 order-lg-2">
                <h2 class="display-5 fw-bold mb-4 text-dark">À Propos de Camertech</h2>
                <div class="lh-lg text-secondary">
                    <p>Fondée sur une vision de modernisation du système éducatif camerounais, <strong>Camertech</strong> est une entreprise technologique dédiée au développement de solutions SaaS innovantes.</p>
                    <p>Notre produit phare, <strong>Copobimat</strong>, est le fruit de plusieurs années de collaboration avec des pédagogues et des administrateurs scolaires. Nous comprenons les défis uniques de l'éducation en Afrique Centrale et nous y répondons par l'excellence technique.</p>
                    <div class="d-flex flex-column gap-3 mt-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><i class="bi bi-check-lg"></i></div>
                            <span class="fw-bold">Expertise Locale & Normes MINESEC</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><i class="bi bi-check-lg"></i></div>
                            <span class="fw-bold">Sécurité des données souveraine</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><i class="bi bi-check-lg"></i></div>
                            <span class="fw-bold">Support réactif 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="bg-dark text-white overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
    <div class="container position-relative">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="badge bg-primary px-3 py-2 rounded-pill mb-4">📍 Parlons de votre projet</div>
                <h2 class="display-4 fw-bold mb-4"><?= __('contact_us_title') ?></h2>
                <p class="text-white-50 fs-5 mb-5"><?= __('contact_us_subtitle') ?></p>
                <div class="d-flex flex-column gap-4">
                    <div class="contact-item d-flex align-items-center gap-4 p-3 rounded-4 transition-all" style="background: rgba(255,255,255,0.05);">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; flex-shrink: 0;"><i class="bi bi-geo-alt fs-4"></i></div>
                        <div>
                            <div class="fw-bold opacity-50 small text-uppercase letter-spacing-1"><?= __('office_location') ?></div>
                            <div class="fs-5"><?= __('office_address') ?></div>
                        </div>
                    </div>
                    <div class="contact-item d-flex align-items-center gap-4 p-3 rounded-4 transition-all" style="background: rgba(255,255,255,0.05);">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; flex-shrink: 0;"><i class="bi bi-telephone fs-4"></i></div>
                        <div>
                            <div class="fw-bold opacity-50 small text-uppercase letter-spacing-1">Appelez-nous</div>
                            <div class="fs-5"><?= __('contact_phone') ?></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="https://wa.me/<?= __('whatsapp_number') ?>" class="btn btn-success btn-lg px-4 py-3 rounded-pill fw-bold hover-scale d-inline-flex align-items-center gap-3">
                            <i class="bi bi-whatsapp fs-4"></i> <?= __('whatsapp_demo') ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="bg-white rounded-5 p-4 p-md-5 text-dark shadow-2xl position-relative">
                    <form id="contactForm" class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">Nom complet</label>
                            <input type="text" name="name" required class="form-control form-control-lg bg-light border-0 px-4 py-3 rounded-4" placeholder="Ex: Jean Dupont">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">Email de contact</label>
                            <input type="email" name="email" required class="form-control form-control-lg bg-light border-0 px-4 py-3 rounded-4" placeholder="jean@email.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary">Votre Message</label>
                            <textarea name="message" required class="form-control bg-light border-0 px-4 py-3 rounded-4" rows="4" placeholder="Dites-nous en plus sur vos besoins..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-premium w-100 py-3 mt-2 fs-5">
                                <span class="btn-text">Envoyer la demande</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button');
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    
    // UI Feedback
    btn.disabled = true;
    btnText.style.opacity = '0.5';
    spinner.classList.remove('d-none');

    const formData = new FormData(form);

    fetch('/contact/send', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Message envoyé !',
                text: 'Merci de votre intérêt. L\'équipe Camertech vous contactera très prochainement.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Parfait',
                borderRadius: '24px'
            });
            form.reset();
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Oups...',
            text: 'Une erreur est survenue lors de l\'envoi du message : ' + error.message,
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btnText.style.opacity = '1';
        spinner.classList.add('d-none');
    });
});
</script>

<style>
    .fw-extra-bold { font-weight: 800; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    
    .contact-item:hover {
        background: rgba(255,255,255,0.1) !important;
        transform: translateX(10px);
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    html {
        scroll-behavior: smooth;
    }
    
    .btn-premium {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        color: white;
        padding: 14px 34px;
        border-radius: 100px;
        font-weight: 700;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
        transition: all 0.3s;
    }
    .btn-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -10px rgba(37, 99, 235, 0.5);
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/public_layout.php';
?>
