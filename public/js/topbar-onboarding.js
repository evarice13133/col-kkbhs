/**
 * Top Bar Onboarding & Activation System
 * Managed state, dynamic copy, progress updates & UX interactions.
 * Inspired by Linear, Notion & Microsoft 365.
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'nm_onboarding_state_v1';
    const DEFAULT_STEPS = [
        { id: 'setup_identity', title: 'Identité de l\'établissement', url: '/settings', completed: true },
        { id: 'academic_years', title: 'Année scolaire & Périodes', url: '/academic_years', completed: true },
        { id: 'setup_classes', title: 'Créer les classes & Niveaux', url: '/classes', completed: false },
        { id: 'setup_teachers', title: 'Attribuer les enseignants', url: '/teachers', completed: false },
        { id: 'register_students', title: 'Inscrire les premiers élèves', url: '/students/create', completed: false }
    ];

    class TopBarOnboardingEngine {
        constructor() {
            this.state = this.loadState();
            this.initSessionTracking();
            document.addEventListener('DOMContentLoaded', () => this.initUI());
        }

        loadState() {
            try {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) {
                    const parsed = JSON.parse(saved);
                    return {
                        steps: parsed.steps || DEFAULT_STEPS,
                        sessionCount: (parsed.sessionCount || 0),
                        bannerDismissed: !!parsed.bannerDismissed
                    };
                }
            } catch (e) {
                console.warn('Onboarding state load failed, fallback to default:', e);
            }
            return { steps: DEFAULT_STEPS, sessionCount: 1, bannerDismissed: false };
        }

        saveState() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(this.state));
            } catch (e) {
                console.error('Failed to save onboarding state:', e);
            }
        }

        initSessionTracking() {
            if (!sessionStorage.getItem('nm_session_active')) {
                sessionStorage.setItem('nm_session_active', '1');
                this.state.sessionCount += 1;
                this.saveState();
            }
        }

        getCompletedCount() {
            return this.state.steps.filter(s => s.completed).length;
        }

        getProgressPercentage() {
            const total = this.state.steps.length;
            if (total === 0) return 100;
            return Math.round((this.getCompletedCount() / total) * 100);
        }

        isComplete() {
            return this.getProgressPercentage() >= 100;
        }

        toggleStep(stepId) {
            const step = this.state.steps.find(s => s.id === stepId);
            if (step) {
                step.completed = !step.completed;
                this.saveState();
                this.updateUI();
            }
        }

        dismissBanner() {
            this.state.bannerDismissed = true;
            this.saveState();
            const banner = document.getElementById('onboardingContextBanner');
            if (banner) {
                banner.style.opacity = '0';
                banner.style.transform = 'translateY(-10px)';
                setTimeout(() => banner.remove(), 300);
            }
        }

        initUI() {
            this.updateUI();
            this.bindEvents();
        }

        bindEvents() {
            const dismissBtn = document.getElementById('dismissOnboardingBanner');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => this.dismissBanner());
            }
        }

        updateUI() {
            const progress = this.getProgressPercentage();
            const completedCount = this.getCompletedCount();
            const totalCount = this.state.steps.length;
            const remainingCount = totalCount - completedCount;

            // 1. Update Micro Progress Line
            const progressLine = document.getElementById('topbarProgressLine');
            if (progressLine) {
                progressLine.style.width = `${progress}%`;
                progressLine.setAttribute('aria-valuenow', progress);
            }

            // 2. Update Pill Badge
            const pillText = document.getElementById('onboardingPillText');
            if (pillText) {
                if (this.isComplete()) {
                    pillText.textContent = '⚡ Espace configuré !';
                } else {
                    pillText.textContent = `⚡ ${progress}% configuré`;
                }
            }

            // 3. Update Checklist Dropdown Header & Items
            const checklistHeader = document.getElementById('onboardingChecklistRemaining');
            if (checklistHeader) {
                if (this.isComplete()) {
                    checklistHeader.textContent = '🎉 Configuration terminée !';
                } else {
                    checklistHeader.textContent = `${remainingCount} étape${remainingCount > 1 ? 's' : ''} restante${remainingCount > 1 ? 's' : ''}`;
                }
            }

            // Render Checklist Items
            const checklistContainer = document.getElementById('onboardingChecklistContainer');
            if (checklistContainer) {
                checklistContainer.innerHTML = this.state.steps.map(step => `
                    <div class="onboarding-step-item ${step.completed ? 'completed' : ''}" onclick="TopBarOnboarding.toggleStep('${step.id}')">
                        <div class="step-check-icon">
                            ${step.completed ? '<i class="bi bi-check-lg"></i>' : ''}
                        </div>
                        <div class="flex-grow-1 text-truncate">
                            <span class="fw-medium">${step.title}</span>
                        </div>
                        <a href="${step.url}" onclick="event.stopPropagation()" class="btn btn-sm btn-link p-0 text-primary opacity-75 hover-opacity-100" title="Accéder">
                            <i class="bi bi-arrow-right-short fs-5"></i>
                        </a>
                    </div>
                `).join('');
            }

            // 4. Update Primary CTA Button
            const ctaBtn = document.getElementById('onboardingPrimaryCTA');
            if (ctaBtn) {
                if (this.isComplete()) {
                    ctaBtn.style.display = 'none';
                } else {
                    ctaBtn.style.display = 'inline-flex';
                    const nextStep = this.state.steps.find(s => !s.completed);
                    if (nextStep) {
                        ctaBtn.href = nextStep.url;
                        ctaBtn.innerHTML = `<i class="bi bi-rocket-takeoff me-1"></i> ${this.state.sessionCount === 1 ? 'Configurer maintenant' : 'Finaliser la config'}`;
                    }
                }
            }

            // 5. Update Contextual Banner
            const banner = document.getElementById('onboardingContextBanner');
            const bannerText = document.getElementById('onboardingBannerText');
            if (banner && bannerText) {
                if (this.state.bannerDismissed || this.isComplete()) {
                    banner.style.display = 'none';
                } else {
                    banner.style.display = 'flex';
                    if (this.state.sessionCount === 1) {
                        bannerText.innerHTML = `<strong>Bienvenue 👋</strong> Commençons la configuration de votre espace académique NoteMaster.`;
                    } else {
                        bannerText.innerHTML = `<strong>Vous y êtes presque !</strong> Il reste <strong>${remainingCount} étape${remainingCount > 1 ? 's' : ''}</strong> pour finaliser la configuration de votre école.`;
                    }
                }
            }
        }
    }

    window.TopBarOnboarding = new TopBarOnboardingEngine();
})();
