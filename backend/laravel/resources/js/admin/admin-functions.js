import { ajaxForm, ajaxRequest } from './main';

$(function () {

	// Authentication
	$('.login-form').on('submit', function () {
		ajaxRequest(route('admin.login'), $(this).serialize(), $(this));
	});
	$('.forgot-pass-form').on('submit', function () {
		ajaxRequest(route('admin.password.email'), $(this).serialize(), $(this));
	});
	$('.set-newpass-form').on('submit', function () {
		ajaxRequest(route('admin.password.update'), $(this).serialize(), $(this));
	});

	// Profile
	$('.profile-update-form').on('submit', function () {
		ajaxRequest(route('admin.profile.update'), new FormData(this), $(this));
	});

});