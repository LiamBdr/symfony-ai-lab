import { Controller } from '@hotwired/stimulus';

const FRAMES = ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'];

export default class extends Controller {
    static targets = ['spinner'];
    static values = {
        interval: { type: Number, default: 80 },
    };

    connect() {
        this.frameIndex = 0;
        this.timer = window.setInterval(() => this.tick(), this.intervalValue);
    }

    disconnect() {
        window.clearInterval(this.timer);
        this.timer = null;
    }

    tick() {
        this.frameIndex = (this.frameIndex + 1) % FRAMES.length;
        this.spinnerTarget.textContent = FRAMES[this.frameIndex];
    }
}
