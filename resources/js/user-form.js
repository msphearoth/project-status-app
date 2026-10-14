/**
 * State for the create/edit user form: the chosen role, the permission
 * checklist (with presets and per-module toggles), password helpers and an
 * unsaved-changes guard.
 */
export default (config) => ({
    name: config.name,
    email: config.email,
    role: config.role,
    selected: config.selected,
    defaults: config.defaults,
    all: config.all,
    sensitive: config.sensitive,
    adminRole: config.adminRole,
    showPassword: false,
    dirty: false,
    submitting: false,

    init() {
        this.$watch('role', () => (this.dirty = true));
        this.$watch('selected', () => (this.dirty = true));
    },

    get isAdmin() {
        return this.role === this.adminRole;
    },

    get enabledCount() {
        return this.isAdmin ? this.all.length : this.selected.length;
    },

    get enabledPercent() {
        return Math.round((this.enabledCount / this.all.length) * 100);
    },

    get sensitiveCount() {
        return this.isAdmin ? this.sensitive.length : this.sensitive.filter((value) => this.selected.includes(value)).length;
    },

    /**
     * Which preset the current selection matches: recommended, all, none or custom.
     */
    get preset() {
        if (this.sameSet(this.selected, this.defaults)) return 'recommended';
        if (this.sameSet(this.selected, this.all)) return 'all';
        if (this.selected.length === 0) return 'none';

        return 'custom';
    },

    get initials() {
        return (this.name || '?')
            .trim()
            .split(/\s+/u)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('');
    },

    applyPreset(preset) {
        this.selected = { recommended: [...this.defaults], all: [...this.all], none: [] }[preset];
    },

    isOn(value) {
        return this.isAdmin || this.selected.includes(value);
    },

    countIn(group) {
        return group.filter((value) => this.selected.includes(value)).length;
    },

    toggleGroup(group) {
        const enable = this.countIn(group) < group.length;

        this.selected = enable
            ? [...new Set([...this.selected, ...group])]
            : this.selected.filter((value) => !group.includes(value));
    },

    sameSet(first, second) {
        return first.length === second.length && first.every((value) => second.includes(value));
    },

    generatePassword() {
        const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*?';
        const password = Array.from(crypto.getRandomValues(new Uint32Array(16)), (value) => characters[value % characters.length]).join('');

        this.$refs.password.value = password;
        this.$refs.passwordConfirmation.value = password;
        this.showPassword = true;
        this.dirty = true;
    },

    warnBeforeLeaving(event) {
        if (this.dirty && !this.submitting) {
            event.preventDefault();
            event.returnValue = '';
        }
    },
});
