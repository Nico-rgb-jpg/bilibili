/* Bilibili — interactions côté client.
 * Le site fonctionne sans JavaScript : ce fichier ne fait qu'améliorer l'expérience
 * (menu mobile, requêtes sans rechargement, lecteur de démonstration…). */

(() => {
    'use strict';

    /* ---------- Menu mobile ---------- */
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('menu');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        });
    }

    /* ---------- Afficher / masquer le mot de passe ---------- */
    document.querySelectorAll('.password-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.textContent = visible ? 'Afficher' : 'Masquer';
            btn.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        });
    });

    /* ---------- Jauge de robustesse du mot de passe ---------- */
    document.querySelectorAll('[data-strength]').forEach((input) => {
        const bar = input.closest('.field').querySelector('.strength span');
        if (!bar) return;
        input.addEventListener('input', () => {
            const v = input.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (v.length >= 12) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const colors = ['#ff4d5e', '#ff4d5e', '#ff9f43', '#ffd166', '#3ecf8e', '#3ecf8e'];
            bar.style.width = `${(score / 5) * 100}%`;
            bar.style.background = colors[score];
        });
    });

    /* ---------- Confirmation avant actions destructrices ---------- */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    /* ---------- Filtres du catalogue : envoi automatique des listes ---------- */
    document.querySelectorAll('form[data-autosubmit] select').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });

    /* ---------- Aperçu de la couleur d'affiche (admin) ---------- */
    const hue = document.querySelector('[data-hue]');
    const huePreview = document.querySelector('[data-hue-preview]');
    if (hue && huePreview) {
        hue.addEventListener('input', () => huePreview.style.setProperty('--h', hue.value));
    }

    /* ---------- Envoi de formulaires en arrière-plan ---------- */
    async function postForm(form, overrides = {}) {
        const data = new FormData(form);
        Object.entries(overrides).forEach(([k, v]) => data.set(k, v));
        const res = await fetch(form.action, {
            method: 'POST',
            body: data,
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    }

    // Ma liste (fiche série)
    document.querySelectorAll('form[data-favori]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button');
            try {
                const { favori } = await postForm(form);
                btn.textContent = favori ? '✓ Dans ma liste' : '+ Ma liste';
                btn.setAttribute('aria-pressed', String(favori));
            } catch {
                form.submit(); // repli : envoi classique
            }
        });
    });

    // Épisode vu / non vu
    function applySeenState(form, vu) {
        form.querySelector('input[name="vu"]').value = vu ? '0' : '1';
        const btn = form.querySelector('button');
        btn.setAttribute('aria-pressed', String(vu));
        if (btn.classList.contains('seen-btn')) {
            btn.textContent = vu ? '✓ Vu' : 'Marquer comme vu';
        } else {
            btn.title = vu ? 'Marquer comme non vu' : 'Marquer comme vu';
            const label = btn.querySelector('.sr-only');
            if (label) label.textContent = vu ? 'Vu' : 'Non vu';
        }
        form.closest('.episode')?.classList.toggle('is-seen', vu);
        document.querySelector('.mini-list .is-current')?.classList.toggle('is-seen', vu);
    }

    document.querySelectorAll('form[data-vu]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const { vu } = await postForm(form);
                applySeenState(form, vu);
            } catch {
                form.submit();
            }
        });
    });

    /* ---------- Lecteur de démonstration ----------
     * Simule la lecture d'un épisode en accéléré (≈ 20 s pour tout l'épisode).
     * À la fin, l'épisode est marqué comme vu et l'épisode suivant se lance. */
    const player = document.querySelector('[data-player]');
    if (player) {
        const duration = Number(player.dataset.duration) || 1440;
        const speed = duration / 20;
        const fill = player.querySelector('[data-fill]');
        const bar = player.querySelector('[data-bar]');
        const timeEl = player.querySelector('[data-time]');
        const playBtns = player.querySelectorAll('[data-play]');
        const note = player.querySelector('.player-note');
        const seenForm = document.querySelector('.watch-head form[data-vu]');
        const nextLink = document.querySelector('[data-next]');

        let current = 0;
        let playing = false;
        let last = 0;
        let ended = false;

        const fmt = (s) => `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, '0')}`;

        function render() {
            const pct = (current / duration) * 100;
            fill.style.width = `${pct}%`;
            bar.setAttribute('aria-valuenow', String(Math.round(pct)));
            timeEl.textContent = fmt(current);
            playBtns.forEach((b) => {
                if (b.classList.contains('player-big-play')) return;
                b.textContent = playing ? '❚❚' : '▶';
                b.setAttribute('aria-label', playing ? 'Pause' : 'Lecture');
            });
            player.classList.toggle('is-playing', playing);
        }

        function frame(t) {
            if (!playing) return;
            if (last) current = Math.min(duration, current + ((t - last) / 1000) * speed);
            last = t;
            render();
            if (current >= duration) return finish();
            requestAnimationFrame(frame);
        }

        function setPlaying(value) {
            playing = value;
            last = 0;
            player.classList.add('has-started');
            if (playing) {
                if (current >= duration) current = 0;
                requestAnimationFrame(frame);
            }
            render();
        }

        async function finish() {
            playing = false;
            render();
            if (ended) return;
            ended = true;
            if (seenForm && seenForm.querySelector('input[name="vu"]').value === '1') {
                try {
                    const { vu } = await postForm(seenForm, { vu: '1' });
                    applySeenState(seenForm, vu);
                } catch { /* l'utilisateur peut toujours cliquer sur « Marquer comme vu » */ }
            }
            if (nextLink) {
                let n = 5;
                const tick = () => {
                    note.textContent = `Épisode suivant dans ${n} s…`;
                    if (n-- === 0) window.location.href = nextLink.href;
                    else setTimeout(tick, 1000);
                };
                tick();
            } else {
                note.textContent = 'Vous avez terminé la série !';
            }
        }

        playBtns.forEach((b) => b.addEventListener('click', () => setPlaying(!playing)));

        bar.addEventListener('click', (e) => {
            const rect = bar.getBoundingClientRect();
            current = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)) * duration;
            render();
            if (current >= duration) finish();
        });

        bar.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') current = Math.min(duration, current + 30);
            else if (e.key === 'ArrowLeft') current = Math.max(0, current - 30);
            else return;
            e.preventDefault();
            render();
        });

        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && !['INPUT', 'TEXTAREA', 'BUTTON', 'SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                setPlaying(!playing);
            }
        });

        render();
    }
})();
