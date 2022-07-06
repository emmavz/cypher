import { ajaxForm, ajaxRequest } from './auth';

$(function () {

	// Authentication
	$('.register-form').on('submit', function () {
		ajaxRequest(route('register'), $(this).serialize(), $(this));
	});
	$('.login-form').on('submit', function () {
		ajaxRequest(route('login'), $(this).serialize(), $(this));
	});
	$('.forgot-pass-form').on('submit', function () {
		ajaxRequest(route('password.email'), $(this).serialize(), $(this));
	});
	$('.set-newpass-form').on('submit', function () {
		ajaxRequest(route('password.update'), $(this).serialize(), $(this));
	});

});