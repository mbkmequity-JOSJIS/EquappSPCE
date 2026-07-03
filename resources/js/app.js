import './bootstrap';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './location-sensor-updater-firebase';

gsap.registerPlugin(ScrollTrigger);

window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

function parseBmkg(root) {
    try {
        const data = root?.data ?? root;
        if (!data) return null;
        const issue = data.issue || data?.forecast?.issue || '—';
        const areas = data?.forecast?.area;
        const first = Array.isArray(areas) ? areas[0] : null;
        const areaName =
            first?.description?.[0] ||
            first?.name?.[0] ||
            first?.name ||
            first?.description ||
            'Wilayah BMKG';
        let weather = 'Prakiraan cuaca';
        let tmin = '—';
        let tmax = '—';
        let hmin = '—';
        let hmax = '—';
        const params = first?.parameter;
        if (Array.isArray(params)) {
            for (const p of params) {
                const id = (p['@attributes'] && p['@attributes'].id) || p.id || p.type;
                const desc = (p['@attributes'] && p['@attributes'].description) || p.description || '';
                const ranges = p.timerange || p.Timerange || [];
                const r0 = Array.isArray(ranges) ? ranges[0] : ranges;
                const val =
                    r0?.value?.[0]?.['@attributes']?.s ||
                    r0?.value?.[0]?.['@attributes']?.n ||
                    r0?.value?.[0] ||
                    r0?.value;
                if (id === 'weather' || (typeof desc === 'string' && desc.toLowerCase().includes('cuaca'))) {
                    if (typeof val === 'string') weather = val;
                }
                if (id === 't' && typeof val === 'string') tmax = val;
                if (id === 'tmin' && typeof val === 'string') tmin = val;
                if (id === 'hu' && typeof val === 'string') {
                    hmax = val;
                    hmin = val;
                }
            }
        }
        return { issue, area: areaName, weather, tmin, tmax, hmin, hmax };
    } catch {
        return null;
    }
}

Alpine.data('bmkgWidget', (endpoint) => ({
    endpoint,
    loading: true,
    error: null,
    demo: false,
    issue: '—',
    area: '—',
    weather: '—',
    tmin: '—',
    tmax: '—',
    hmin: '—',
    hmax: '—',
    async load() {
        this.loading = true;
        this.error = null;
        try {
            const res = await fetch(this.endpoint, { headers: { Accept: 'application/json' } });
            const body = await res.json();
            if (!body.ok) {
                this.error = body.message || 'Data BMKG tidak tersedia.';
                return;
            }
            this.demo = !!body.demo;
            if (body.demo && body.summary) {
                const s = body.summary;
                this.issue = s.issue || '—';
                this.area = s.area || '—';
                this.weather = s.weather || '—';
                this.tmin = s.temp_min ?? '—';
                this.tmax = s.temp_max ?? '—';
                this.hmin = s.humidity_min ?? '—';
                this.hmax = s.humidity_max ?? '—';
                return;
            }
            const parsed = parseBmkg(body.data);
            if (!parsed) {
                this.error = 'Struktur data BMKG tidak dikenali.';
                return;
            }
            Object.assign(this, parsed);
        } catch {
            this.error = 'Gagal memuat widget cuaca.';
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('locationCharts', (payload) => ({
    payload,
    range: '6',
    chart: null,
    init() {
        setTimeout(() => {
            this.$nextTick(() => this.render());
        }, 100);
        this.$watch('range', () => this.render());
    },
    render() {
        const canvas = this.$refs.chartCanvas;
        if (!canvas || !window.Chart) {
            console.warn('Canvas atau Chart.js tidak tersedia');
            return;
        }
        const pack = this.payload.chart?.[this.range];
        if (!pack) {
            console.warn('Data chart tidak ditemukan untuk range:', this.range);
            return;
        }
        if (this.chart) {
            this.chart.destroy();
        }
        const ctx = canvas.getContext('2d');
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: pack.labels,
                datasets: [
                    {
                        label: this.payload.type === 'AQUAVISKA' ? 'Trend Sensor Kualitas Air' : 'Trend Sensor IOT Climate',
                        data: pack.data,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.18)',
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#0369a1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#475569',
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#475569',
                        },
                        grid: {
                            color: '#e2e8f0',
                        },
                    },
                    y: {
                        ticks: {
                            color: '#475569',
                        },
                        grid: {
                            color: '#e2e8f0',
                        },
                    },
                },
            },
        });
    },
}));

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();
