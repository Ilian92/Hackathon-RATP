<div
    x-data="complaintsMap()"
    x-init="init()"
    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
>

    <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Carte des lignes — Densité de plaintes</h2>
            <p class="text-xs text-gray-400 mt-0.5">Vert = peu de plaintes · Rouge = beaucoup de plaintes</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">

            <select
                x-model="filters.period"
                @change="fetchData()"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/30"
            >
                <option value="all">Toute la période</option>
                <option value="90d">90 derniers jours</option>
                <option value="30d" selected>30 derniers jours</option>
                <option value="7d">7 derniers jours</option>
            </select>

            <select
                x-model="filters.nature"
                @change="fetchData()"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/30"
            >
                <option value="all">Toutes natures</option>
                <option value="negative">Négatifs seulement</option>
                <option value="positive">Positifs seulement</option>
            </select>

            <select
                x-model="filters.severity"
                @change="fetchData()"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/30"
            >
                <option value="all">Tous niveaux</option>
                <option value="0">Niveau 0 — Sans suite</option>
                <option value="1">Niveau 1 — Mineur</option>
                <option value="2">Niveau 2 — Modéré</option>
                <option value="3">Niveau 3 — Grave</option>
                <option value="4">Niveau 4 — Très grave</option>
            </select>

            <button
                @click="resetFilters()"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 hover:border-gray-300 transition"
            >
                Réinitialiser
            </button>
        </div>
    </div>

    <div x-show="loading" class="relative">
        <div class="absolute inset-0 bg-white/70 z-[1000] flex items-center justify-center" style="height: 640px;">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="animate-spin h-4 w-4 text-[#004fa3]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Chargement…
            </div>
        </div>
    </div>

    <div id="complaints-map" style="height: 640px; z-index: 0;"></div>

    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500">Peu de plaintes</span>
            <div class="w-32 h-2.5 rounded-full" style="background: linear-gradient(to right, #22c55e, #eab308, #ef4444);"></div>
            <span class="text-xs text-gray-500">Beaucoup de plaintes</span>
        </div>
        <p class="text-xs text-gray-400" x-text="summary"></p>
    </div>
</div>

<script>
function complaintsMap() {
    return {
        map: null,
        polylines: [],
        markers: [],
        loading: false,
        summary: '',
        filters: {
            period: '30d',
            nature: 'all',
            severity: 'all',
        },

        init() {
            this.$nextTick(() => {
                this.map = L.map('complaints-map', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                }).setView([48.85, 2.35], 11);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                }).addTo(this.map);

                this.fetchData();
            });
        },

        async fetchData() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                const res = await fetch(`/map-data?${params}`);
                const data = await res.json();
                this.renderLines(data);
            } finally {
                this.loading = false;
            }
        },

        renderLines(data) {
            // Remove existing layers
            this.polylines.forEach(p => p.remove());
            this.markers.forEach(m => m.remove());
            this.polylines = [];
            this.markers = [];

            const maxComplaints = data.max_complaints || 1;
            let totalComplaints = 0;

            data.lines.forEach(line => {
                if (line.stops.length < 2) { return; }

                totalComplaints += line.complaint_count;
                const color = this.getColor(line.complaint_count, maxComplaints);
                const coords = line.stops.map(s => [s.lat, s.lng]);

                // Draw polyline
                const poly = L.polyline(coords, {
                    color,
                    weight: 5,
                    opacity: 0.85,
                    lineJoin: 'round',
                }).addTo(this.map);

                // Popup
                const negativeRatio = data.max_complaints > 0
                    ? Math.round(line.complaint_count / maxComplaints * 100)
                    : 0;

                poly.bindPopup(`
                    <div style="min-width: 140px;">
                        <p style="font-weight: 700; font-size: 14px; margin: 0 0 4px;">Ligne ${line.nom}</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0 0 6px;">${line.stops.length} arrêts</p>
                        <p style="font-size: 13px; font-weight: 600; color: ${color}; margin: 0;">
                            ${line.complaint_count} plainte${line.complaint_count !== 1 ? 's' : ''}
                        </p>
                    </div>
                `);

                this.polylines.push(poly);

                // Draw stop markers
                line.stops.forEach((stop, idx) => {
                    const isTerminus = idx === 0 || idx === line.stops.length - 1;
                    const circle = L.circleMarker([stop.lat, stop.lng], {
                        radius: isTerminus ? 5 : 3,
                        fillColor: isTerminus ? color : '#ffffff',
                        fillOpacity: isTerminus ? 1 : 0.9,
                        color: color,
                        weight: 2,
                    }).addTo(this.map);

                    circle.bindTooltip(`<span style="font-size:11px;">${stop.nom}</span>`, {
                        direction: 'top',
                        offset: [0, -4],
                    });

                    this.markers.push(circle);
                });
            });

            const lineCount = data.lines.filter(l => l.stops.length >= 2).length;
            this.summary = `${lineCount} ligne${lineCount > 1 ? 's' : ''} · ${totalComplaints} plainte${totalComplaints !== 1 ? 's' : ''} au total`;
        },

        getColor(count, max) {
            if (max === 0 || count === 0) {
                return '#22c55e'; // green
            }
            const ratio = count / max; // 0..1

            let r, g, b;
            if (ratio <= 0.5) {
                // green (#22c55e) → yellow (#eab308)
                const t = ratio * 2;
                r = Math.round(34 + (234 - 34) * t);
                g = Math.round(197 + (179 - 197) * t);
                b = Math.round(94 + (8 - 94) * t);
            } else {
                // yellow (#eab308) → red (#ef4444)
                const t = (ratio - 0.5) * 2;
                r = Math.round(234 + (239 - 234) * t);
                g = Math.round(179 + (68 - 179) * t);
                b = Math.round(8 + (68 - 8) * t);
            }

            return `rgb(${r},${g},${b})`;
        },

        resetFilters() {
            this.filters = { period: '30d', nature: 'all', severity: 'all' };
            this.fetchData();
        },
    };
}
</script>
