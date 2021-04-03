export function trackFocus(detectFocus, detectBlur, container = window) {
	container.addEventListener ? container.addEventListener('focus', detectFocus, true) : container.attachEvent('onfocusout', detectFocus);

	container.addEventListener ? container.addEventListener('blur', detectBlur, true) : container.attachEvent('onblur', detectBlur);
}
