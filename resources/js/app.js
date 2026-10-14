import Alpine from 'alpinejs';
import userForm from './user-form';

window.Alpine = Alpine;

Alpine.data('userForm', userForm);

Alpine.start();
