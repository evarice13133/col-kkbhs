/**
 * INTELLIGENT ONBOARDING & GUIDED TOUR SYSTEM
 * Evaluates real server state, user roles, permissions, and provides step-by-step interactive popovers.
 */
(function () {
    'use strict';

    class OnboardingEngine {
        constructor() {
            this.serverData = window.NM_ONBOARDING_DATA || {
                userId: 0,
                userRole: 'guest',
                steps: [],
                completedCount: 0,
                totalCount: 0,
                percentage: 100,
                isComplete: true
            };

            this.storageKey = `nm_onboarding_u${this.serverData.userId || 0}`;
            this.localState = this.loadLocalState();
            this.currentStepIdx = 0;
            this.activePopover = null;
            this.backdropOverlay = null;

            document.addEventListener('DOMContentLoaded', () => this.init());
        }

        loadLocalState() {
            try {
                const saved = localStorage.getItem(this.storageKey);
                if (saved) return JSON.parse(saved);
            } catch (e) {
                console.warn('Failed to load onboarding local state:', e);
            }
            return {
                dismissedBanner: false,
                tourFinished: false,
                lastStepIdx: 0
            };
        }

        saveLocalState() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.localState));
            } catch (e) {
                console.error('Failed to save onboarding local state:', e);
            }
        }

        init() {
            this.ensureDOMOverlay();
            this.updateUI();
            this.bindEvents();

            // Auto-trigger tour if session is fresh, uncompleted, and not dismissed
            if (!this.localState.tourFinished && !this.serverData.isComplete) {
                const firstUnfinishedIdx = this.serverData.steps.findIndex(s => !s.completed);
                if (firstUnfinishedIdx !== -1) {
                    this.currentStepIdx = firstUnfinishedIdx;
                }
            }
        }

        ensureDOMOverlay() {
            if (!document.getElementById('onboardingBackdropOverlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'onboardingBackdropOverlay';
                overlay.className = 'onboarding-backdrop-overlay';
                document.body.appendChild(overlay);
                this.backdropOverlay = overlay;
            } else {
                this.backdropOverlay = document.getElementById('onboardingBackdropOverlay');
            }

            if (!document.getElementById('onboardingPopoverCard')) {
                const card = document.createElement('div');
                card.id = 'onboardingPopoverCard';
                card.className = 'onboarding-popover-card';
                card.style.display = 'none';
                document.body.appendChild(card);
            }
        }

        bindEvents() {
            const dismissBannerBtn = document.getElementById('dismissOnboardingBanner');
            if (dismissBannerBtn) {
                dismissBannerBtn.addEventListener('click', () => this.dismissBanner());
            }

            const startTourBtn = document.getElementById('startGuidedTour');
            if (startTourBtn) {
                startTourBtn.addEventListener('click', () => this.relaunch());
            }

            window.addEventListener('resize', () => {
                if (this.activePopover && this.activePopover.style.display !== 'none') {
                    this.positionPopover();
                }
            });
        }

        dismissBanner() {
            this.localState.dismissedBanner = true;
            this.saveLocalState();
            const banner = document.getElementById('onboardingContextBanner');
            if (banner) {
                banner.style.opacity = '0';
                setTimeout(() => banner.style.display = 'none', 300);
            }
        }

        /**
         * Relaunch the guided tour from Step 1 or first unfinished step
         */
        relaunch() {
            this.localState.tourFinished = false;
            this.saveLocalState();
            
            // Find first uncompleted step or start at 0
            const firstUnfinished = this.serverData.steps.findIndex(s => !s.completed);
            this.currentStepIdx = firstUnfinished !== -1 ? firstUnfinished : 0;
            this.showTourStep(this.currentStepIdx);
        }

        /**
         * Start or Resume the guided tour
         */
        start() {
            this.relaunch();
        }

        showTourStep(idx) {
            const steps = this.serverData.steps;
            if (!steps || steps.length === 0) return;

            if (idx < 0) idx = 0;
            if (idx >= steps.length) {
                this.finishTour();
                return;
            }

            this.currentStepIdx = idx;
            const step = steps[idx];

            // Highlight target element if present
            document.querySelectorAll('.onboarding-element-highlight').forEach(el => {
                el.classList.remove('onboarding-element-highlight');
            });

            let targetEl = step.target ? document.querySelector(step.target) : null;
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                targetEl.classList.add('onboarding-element-highlight');
            }

            if (this.backdropOverlay) {
                this.backdropOverlay.classList.add('show');
            }

            const card = document.getElementById('onboardingPopoverCard');
            if (!card) return;

            const isLast = (idx === steps.length - 1);
            const isFirst = (idx === 0);
            const statusBadge = step.completed ? '<span class="badge bg-success bg-opacity-10 text-success ms-2">✓ Déjà terminé</span>' : '';

            card.innerHTML = `
                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold extra-small">
                        Étape ${idx + 1} / ${steps.length} (${Math.round(((idx + 1) / steps.length) * 100)}%)
                    </span>
                    ${statusBadge}
                    <button type="button" class="btn-close extra-small ms-auto" onclick="window.TopBarOnboarding.closeTour()"></button>
                </div>
                <h6 class="fw-bold fs-6 text-main-theme mb-1">${step.title}</h6>
                <p class="text-muted extra-small mb-3" style="line-height: 1.45;">${step.desc || ''}</p>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <div>
                        ${!isFirst ? `<button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 me-1 extra-small" onclick="window.TopBarOnboarding.prevStep()">← Précédent</button>` : ''}
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="${step.url}" class="btn btn-sm btn-primary py-1 px-2.5 extra-small fw-bold">
                            Exécuter <i class="bi bi-arrow-right"></i>
                        </a>
                        ${!isLast ? `<button type="button" class="btn btn-sm btn-light py-1 px-2 extra-small text-muted" onclick="window.TopBarOnboarding.nextStep()">Passer →</button>` : `<button type="button" class="btn btn-sm btn-success py-1 px-2 extra-small" onclick="window.TopBarOnboarding.finishTour()">Terminer 🎉</button>`}
                    </div>
                </div>
            `;

            card.style.display = 'block';
            this.activePopover = card;
            this.positionPopover(targetEl);
        }

        positionPopover(targetEl) {
            const card = document.getElementById('onboardingPopoverCard');
            if (!card) return;

            if (targetEl) {
                const rect = targetEl.getBoundingClientRect();
                const cardHeight = card.offsetHeight || 220;
                const cardWidth = card.offsetWidth || 360;

                let top = rect.bottom + 12;
                if (top + cardHeight > window.innerHeight - 20) {
                    top = Math.max(20, rect.top - cardHeight - 12);
                }

                let left = Math.max(20, rect.left);
                if (left + cardWidth > window.innerWidth - 20) {
                    left = window.innerWidth - cardWidth - 20;
                }

                card.style.top = `${top}px`;
                card.style.left = `${left}px`;
            } else {
                // Center on screen if target not found
                card.style.top = '50%';
                card.style.left = '50%';
                card.style.transform = 'translate(-50%, -50%)';
            }
        }

        nextStep() {
            this.showTourStep(this.currentStepIdx + 1);
        }

        prevStep() {
            this.showTourStep(this.currentStepIdx - 1);
        }

        closeTour() {
            const card = document.getElementById('onboardingPopoverCard');
            if (card) card.style.display = 'none';

            if (this.backdropOverlay) {
                this.backdropOverlay.classList.remove('show');
            }

            document.querySelectorAll('.onboarding-element-highlight').forEach(el => {
                el.classList.remove('onboarding-element-highlight');
            });
        }

        finishTour() {
            this.localState.tourFinished = true;
            this.saveLocalState();
            this.closeTour();
            this.updateUI();
        }

        updateUI() {
            const data = this.serverData;
            const progress = data.percentage || 0;
            const completedCount = data.completedCount || 0;
            const totalCount = data.totalCount || 0;
            const remainingCount = totalCount - completedCount;

            // 1. Progress Line
            const progressLine = document.getElementById('topbarProgressLine');
            if (progressLine) {
                progressLine.style.width = `${progress}%`;
                progressLine.setAttribute('aria-valuenow', progress);
            }

            // 2. Pill Badge
            const pillText = document.getElementById('onboardingPillText');
            if (pillText) {
                if (data.isComplete) {
                    pillText.textContent = '✨ Espace configuré (100%)';
                } else {
                    pillText.textContent = `⚡ ${completedCount}/${totalCount} (${progress}%)`;
                }
            }

            // 3. Checklist Menu Header & Container
            const checklistHeader = document.getElementById('onboardingChecklistRemaining');
            if (checklistHeader) {
                if (data.isComplete) {
                    checklistHeader.textContent = '🎉 Configuration terminée !';
                } else {
                    checklistHeader.textContent = `${remainingCount} étape${remainingCount > 1 ? 's' : ''} restante${remainingCount > 1 ? 's' : ''}`;
                }
            }

            const checklistContainer = document.getElementById('onboardingChecklistContainer');
            if (checklistContainer) {
                const stepsHTML = (data.steps || []).map((step, idx) => `
                    <div class="onboarding-step-item ${step.completed ? 'completed' : ''}" onclick="window.TopBarOnboarding.showTourStep(${idx})">
                        <div class="step-check-icon">
                            ${step.completed ? '<i class="bi bi-check-lg"></i>' : (idx + 1)}
                        </div>
                        <div class="flex-grow-1 text-truncate">
                            <span class="fw-medium">${step.title}</span>
                        </div>
                        <a href="${step.url}" onclick="event.stopPropagation()" class="btn btn-sm btn-link p-0 text-primary opacity-75 hover-opacity-100" title="Accéder">
                            <i class="bi bi-arrow-right-short fs-5"></i>
                        </a>
                    </div>
                `).join('');

                const relaunchBtnHTML = `
                    <div class="mt-2 pt-2 border-top text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill extra-small py-1" onclick="window.TopBarOnboarding.relaunch()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Relancer le guide d'onboarding
                        </button>
                    </div>
                `;

                checklistContainer.innerHTML = stepsHTML + relaunchBtnHTML;
            }

            // 4. Primary CTA Button
            const ctaBtn = document.getElementById('onboardingPrimaryCTA');
            if (ctaBtn) {
                if (data.isComplete) {
                    ctaBtn.style.display = 'none';
                } else {
                    ctaBtn.style.display = 'inline-flex';
                    const nextStep = (data.steps || []).find(s => !s.completed);
                    if (nextStep) {
                        ctaBtn.href = nextStep.url;
                        ctaBtn.innerHTML = `<i class="bi bi-rocket-takeoff me-1"></i> ${nextStep.title}`;
                    }
                }
            }

            // 5. Contextual Banner
            const banner = document.getElementById('onboardingContextBanner');
            const bannerText = document.getElementById('onboardingBannerText');
            if (banner && bannerText) {
                if (this.localState.dismissedBanner || data.isComplete) {
                    banner.style.display = 'none';
                } else {
                    banner.style.display = 'flex';
                    bannerText.innerHTML = `<strong>Accompagnement Onboarding :</strong> Vous avez complété <strong>${completedCount}/${totalCount} étapes</strong> (${progress}%).`;
                }
            }
        }
    }

    const instance = new OnboardingEngine();
    window.TopBarOnboarding = instance;

    // Helper functions exposed globally
    window.TopBarOnboarding.nextStep = () => instance.nextStep();
    window.TopBarOnboarding.prevStep = () => instance.prevStep();
    window.TopBarOnboarding.closeTour = () => instance.closeTour();
    window.TopBarOnboarding.finishTour = () => instance.finishTour();
    window.TopBarOnboarding.relaunch = () => instance.relaunch();
    window.TopBarOnboarding.showTourStep = (idx) => instance.showTourStep(idx);
})();
