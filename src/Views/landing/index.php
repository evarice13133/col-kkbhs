<?php
/**
 * Vitrine Publique NoteMaster par Camertech
 * Optimisée SEO & Expérience Utilisateur Premium
 */
ob_start();
?>

<!-- Hero Section -->
<section id="home" class="position-relative pt-5 pb-5 overflow-hidden">
    <div class="container pt-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-3 fw-extra-bold mb-4" style="line-height: 1.1;">
                   Le Système de Gestion Scolaire <span class="text-gradient">Intelligent & Bilingue</span>
                </h1>
                <p class="lead text-secondary mb-5">
                    Une solution complète qui automatise vos bulletins, gère la discipline et simplifie vos conseils de classe en un clic.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/login" class="btn btn-premium btn-lg px-5 py-3 shadow-xl">Essayer NoteMaster</a>
                    <a href="#contact" class="btn btn-outline-dark btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm hover-scale">
                        Démonstration Gratuite
                    </a>
                </div>
                <div class="mt-5 d-flex align-items-center gap-4">
                    <div class="d-flex -space-x-3">
                        <img src="https://i.pravatar.cc/100?u=1" class="rounded-circle border border-white" style="width: 40px; margin-right: -15px;">
                        <img src="https://i.pravatar.cc/100?u=2" class="rounded-circle border border-white" style="width: 40px; margin-right: -15px;">
                        <img src="https://i.pravatar.cc/100?u=3" class="rounded-circle border border-white" style="width: 40px;">
                    </div>
                    <span class="small text-secondary fw-medium">Adopté par 26 établissements pilotes</span>
                </div>
            </div>
            <div class="col-lg-6 position-relative" data-aos="zoom-in" data-aos-delay="200">
                <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary opacity-5 rounded-circle" style="filter: blur(80px);"></div>
                <div class="hero-image-wrapper p-2 bg-white rounded-5 shadow-2xl border cursor-zoom" onclick="openLightbox('/public/assets/Dashbord_light.png')">
                    <img src="/public/assets/Dashbord_light.png" alt="NoteMaster Dashboard" class="img-fluid rounded-4 animate-float">
                    <div class="zoom-overlay rounded-4"><i class="bi bi-search fs-1"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-white overflow-hidden">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3" data-aos="fade-up">
                <div class="p-4 glass-card border-0 animate-float-slow bg-light">
                    <h2 class="display-6 fw-bold text-primary mb-1"><span class="counter" data-target="26">0</span>+</h2>
                    <p class="text-secondary small fw-bold text-uppercase">Établissements</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 glass-card border-0 animate-float-medium bg-light">
                    <h2 class="display-6 fw-bold text-primary mb-1"><span class="counter" data-target="50">0</span>k+</h2>
                    <p class="text-secondary small fw-bold text-uppercase">Élèves Suivis</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="p-4 glass-card border-0 animate-float-fast bg-light">
                    <h2 class="display-6 fw-bold text-primary mb-1"><span class="counter" data-target="99">0</span>%</h2>
                    <p class="text-secondary small fw-bold text-uppercase">Satisfaction</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="p-4 glass-card border-0 animate-float-medium bg-light">
                    <h2 class="display-6 fw-bold text-primary mb-1">24/7</h2>
                    <p class="text-secondary small fw-bold text-uppercase">Support Réactif</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Intelligence & Bilinguisme Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-up">
                <div class="p-4 h-100 glass-card bg-light border-0 d-flex gap-4">
                    <div class="icon-box text-primary fs-2"><i class="bi bi-translate"></i></div>
                    <div>
                        <h4 class="fw-bold mb-2">Bilinguisme Intégré</h4>
                        <p class="text-secondary small">Changez la langue du système en un clic. NoteMaster génère des bulletins bilingues ou unilingues (FR/EN) pour répondre aux exigences de chaque section.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 h-100 glass-card bg-light border-0 d-flex gap-4">
                    <div class="icon-box text-success fs-2"><i class="bi bi-robot"></i></div>
                    <div>
                        <h4 class="fw-bold mb-2">Conseil de Classe Automatisé</h4>
                        <p class="text-secondary small">Le système analyse les moyennes pour suggérer automatiquement la discipline, les décisions du conseil et les appréciations globales.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Features (Images) -->
<section class="py-5 bg-gradient-soft" id="services">
    <div class="container">
        <!-- Bulletin Section -->
        <div class="row g-5 align-items-center mb-5 pb-lg-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="image-showcase p-2 bg-white rounded-4 shadow-lg border cursor-zoom" onclick="openLightbox('/public/assets/bulletin%20de%20note.png')">
                    <img src="/public/assets/bulletin%20de%20note.png" alt="Bulletin NoteMaster" class="img-fluid rounded-3">
                    <div class="zoom-overlay rounded-3"><i class="bi bi-search fs-1"></i></div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill mb-3">Intelligence Pédagogique</div>
                <h2 class="display-5 fw-bold mb-4">Le Bulletin <span class="text-primary">Nouvelle Génération</span></h2>
                <p class="text-secondary fs-5 mb-4">Un document intelligent qui parle de l'élève sans intervention manuelle lourde.</p>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Discipline & Assiduité</h6>
                            <p class="small text-secondary mb-0">Gestion automatique des absences et avertissements de conduite.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Saisie par Évaluation Active</h6>
                            <p class="small text-secondary mb-0">L'enseignant saisit les notes en fonction de l'évaluation en cours, le système s'occupe du reste.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Traduction Instantanée</h6>
                            <p class="small text-secondary mb-0">Les bulletins de la section francophone peuvent être exportés en anglais pour les enseignants de langue.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PV Section -->
        <div class="row g-5 align-items-center mb-5 pb-lg-5">
            <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-right">
                <div class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill mb-3">Outils de Pilotage</div>
                <h2 class="display-5 fw-bold mb-4">Procès Verbaux & <span class="text-warning">Statistiques</span></h2>
                <p class="text-secondary fs-5 mb-4">Simplifiez vos conseils de classe avec des PV générés automatiquement.</p>
                <p class="text-secondary lh-lg mb-4">
                    Accédez à des statistiques détaillées par classe, par matière et par enseignant. Visualisez instantanément les taux de réussite pour prendre les meilleures décisions pédagogiques lors de vos conseils de classe.
                </p>
                <div class="p-4 glass-card bg-white border rounded-4 d-flex align-items-center gap-4">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="bi bi-bar-chart-fill fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">PV Prêts en 1 seconde</h6>
                        <p class="small text-secondary mb-0">Toutes les moyennes et rangs pré-calculés.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-left">
                <div class="image-showcase p-2 bg-white rounded-4 shadow-lg border cursor-zoom" onclick="openLightbox('/public/assets/dashbord_night.png')">
                    <img src="/public/assets/dashbord_night.png" alt="Statistiques NoteMaster" class="img-fluid rounded-3">
                    <div class="zoom-overlay rounded-3"><i class="bi bi-search fs-1"></i></div>
                </div>
            </div>
        </div>

        <!-- Tableau d'Honneur Section -->
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="image-showcase p-2 bg-white rounded-4 shadow-lg border cursor-zoom" onclick="openLightbox('/public/assets/tableau%20d\'honneur.png')">
                    <img src="/public/assets/tableau%20d'honneur.png" alt="Tableau d'Honneur NoteMaster" class="img-fluid rounded-3">
                    <div class="zoom-overlay rounded-3"><i class="bi bi-search fs-1"></i></div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill mb-3">Excellence Académique</div>
                <h2 class="display-5 fw-bold mb-4">Tableaux d'Honneur <span class="text-info">Automatiques</span></h2>
                <p class="text-secondary fs-5 mb-4">Récompensez le mérite sans effort administratif supplémentaire.</p>
                <p class="text-secondary lh-lg mb-4">
                    NoteMaster identifie automatiquement les élèves méritants en fonction de leurs moyennes et de leur conduite. Le système génère instantanément des **Tableaux d'Honneur** et des **Certificats d'Excellence** au design prestigieux.
                </p>
                <div class="d-flex gap-3">
                    <div class="p-3 glass-card bg-white border rounded-4 flex-fill">
                        <i class="bi bi-star-fill text-warning fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Prestige</h6>
                        <p class="small text-secondary mb-0">Design haute qualité.</p>
                    </div>
                    <div class="p-3 glass-card bg-white border rounded-4 flex-fill">
                        <i class="bi bi-gear-wide-connected text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Zero Effort</h6>
                        <p class="small text-secondary mb-0">Génération 100% auto.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">Passez à la <span class="text-primary">Vitesse Supérieure</span></h2>
                <p class="text-secondary fs-5">L'équipe NoteMaster est prête à vous accompagner dans cette transformation.</p>
            </div>
            <div class="col-lg-10" data-aos="fade-up">
                <div class="glass-card p-4 p-md-5 bg-light border shadow-sm rounded-5">
                    <form id="contactForm">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Établissement</label>
                                <input type="text" name="name" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Nom de l'école" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Email</label>
                                <input type="email" name="email" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="directeur@ecole.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                <input type="tel" name="phone" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Ex: 6XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Ville</label>
                                <input type="text" name="city" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Ex: Douala">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Comment pouvons-nous vous aider ?</label>
                                <textarea name="message" class="form-control form-control-lg bg-white border-0 shadow-sm" rows="4" placeholder="Détails de votre demande..." required></textarea>
                            </div>
                            <div class="col-12 text-center pt-3">
                                <button type="submit" class="btn btn-premium btn-lg px-5 py-3 w-100 w-md-auto" id="submitBtn">
                                    <span id="btnText">Envoyer ma demande</span>
                                    <span class="spinner-border spinner-border-sm d-none" id="btnSpinner"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Rhetorical Question Popup -->
<div id="rhetoricalPopup" class="rhetorical-popup">
    <div class="glass-card p-4 border-primary shadow-2xl animate__animated animate__fadeInUp position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" onclick="closeRhetoricalPopup()" aria-label="Close"></button>
        <div class="d-flex align-items-center gap-3 pe-4">
            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 fs-4">
                <i class="bi bi-question-circle-fill"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1">Fatigué des retards de notes ?</h5>
                <p class="text-secondary small mb-0">Avec NoteMaster, fini les retards de traitement !</p>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox">&times;</span>
    <img id="lightboxImg" class="lightbox-content">
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Popup Logic
window.addEventListener('load', () => {
    setTimeout(() => {
        const popup = document.getElementById('rhetoricalPopup');
        popup.classList.add('active');
    }, 2000);
});

function closeRhetoricalPopup() {
    const popup = document.getElementById('rhetoricalPopup');
    const card = popup.querySelector('.glass-card');
    card.classList.replace('animate__fadeInUp', 'animate__fadeOutDown');
    setTimeout(() => popup.style.display = 'none', 500);
}

// Lightbox Logic
function openLightbox(src) {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    img.src = src;
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');
    
    btn.disabled = true;
    btnText.style.opacity = '0.5';
    spinner.classList.remove('d-none');

    const formData = new FormData(form);

    fetch('/send-contact', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Merci !',
                text: 'Votre demande a été transmise avec succès. Notre équipe vous contactera.',
                icon: 'success',
                confirmButtonColor: '#2563eb'
            });
            form.reset();
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Oups...',
            text: 'Une erreur est survenue : ' + error.message,
            icon: 'error'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btnText.style.opacity = '1';
        spinner.classList.add('d-none');
    });
});

// Animation des compteurs
const counters = document.querySelectorAll('.counter');
const speed = 200;

const startCounters = () => {
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / speed;
            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
};

const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            startCounters();
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.counter')?.closest('section');
if (statsSection) statsObserver.observe(statsSection);
</script>

<style>
    .rhetorical-popup {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 3000;
        max-width: 420px;
        display: none;
    }
    .rhetorical-popup.active {
        display: block;
    }
    
    .cursor-zoom { cursor: zoom-in; }
    .hero-image-wrapper, .image-showcase {
        position: relative;
        transition: all 0.3s ease;
    }
    .hero-image-wrapper:hover, .image-showcase:hover {
        transform: scale(1.02);
    }
    .zoom-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(37, 99, 235, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
        color: white;
    }
    .image-showcase:hover .zoom-overlay, .hero-image-wrapper:hover .zoom-overlay {
        opacity: 1;
    }

    /* Lightbox Styles */
    .lightbox {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 5000;
        padding: 20px;
    }
    .lightbox.active { display: flex; }
    .lightbox-content {
        max-width: 95%;
        max-height: 90vh;
        border-radius: 12px;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
    }
    .close-lightbox {
        position: absolute;
        top: 20px; right: 30px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .fw-extra-bold { font-weight: 800; }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); }
    .animate-float { animation: float 6s ease-in-out infinite; }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    @media (max-width: 768px) {
        .display-3 { font-size: 1.85rem !important; }
        .display-5 { font-size: 1.6rem !important; }
        .btn-lg { width: 100%; }
        .lightbox-content { max-width: 100%; }
        .rhetorical-popup {
            bottom: 20px;
            right: 20px;
            left: 20px;
            max-width: none;
        }
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/public_layout.php';
?>
