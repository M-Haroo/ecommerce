@extends('layouts.app')

@section('content')
<style>
  #password-help {
    color: #ff0000ec;           /* brighter pure red */
    font-weight: 600;         /* slightly bold */
    
  }
  
  
</style>

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register container">
      <ul class="nav nav-tabs mb-5" id="login_register" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link nav-link_underscore active" id="register-tab" data-bs-toggle="tab"
            href="#tab-item-register" role="tab" aria-controls="tab-item-register" aria-selected="true">Register</a>
        </li>
      </ul>
      <div class="tab-content pt-2" id="login_register_tab_content">
        <div class="tab-pane fade show active" id="tab-item-register" role="tabpanel" aria-labelledby="register-tab">
          <div class="register-form">
            <form method="POST" action="{{route('register')}}" name="register-form" class="needs-validation" novalidate="">
                @csrf
              <div class="form-floating mb-3">
                <input class="form-control form-control_gray @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required="" autocomplete="name"
                  autofocus="">
                <label for="name">Name</label>
                @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
              </div>
              <div class="pb-3"></div>
              <div class="form-floating mb-3">
                <input id="email" type="email" class="form-control form-control_gray @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required=""
                  autocomplete="email">
                <label for="email">Email address *</label>
                @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
              </div>

              <div class="pb-3"></div>

       <div class="form-floating mb-3">
  <input
    id="mobile"
    type="tel"
    class="form-control form-control_gray @error('mobile') is-invalid @enderror"
    name="mobile"
    value="{{ old('mobile') }}"
    required
    autocomplete="tel"
    inputmode="numeric"
    pattern="[0-9]{11}"
    maxlength="11"
  >
  <label for="mobile">Mobile *</label>
  @error('mobile')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
  @enderror
  <div id="mobile-help" class="small mt-1 text-danger"></div>
</div>


<div class="pb-3"></div>

<div class="form-floating mb-3 position-relative">
  <input
    id="password"
    type="password"
    class="form-control form-control_gray @error('password') is-invalid @enderror"
    name="password"
    required
    autocomplete="new-password"
    minlength="8"
    pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
  >
  <label for="password">Password *</label>
  <span class="toggle-password" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer;">
    <i class="fa fa-eye"></i>
  </span>
  @error('password')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
  @enderror
  <div id="password-help" class="small mt-1 text-danger"></div>
</div>
<div class="pb-3"></div>
<div class="form-floating mb-3 position-relative">
  <input id="password-confirm" type="password" class="form-control form-control_gray"
    name="password_confirmation" required autocomplete="new-password">
  <label for="password-confirm">Confirm Password *</label>
  <span class="toggle-password-confirm" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer;">
    <i class="fa fa-eye"></i>
  </span>
  <div id="password-confirm-help" class="small mt-1 text-danger"></div>
</div>




              

              <div class="d-flex align-items-center mb-3 pb-2">
                <p class="m-0">Your personal data will be used to support your experience throughout this website, to
                  manage access to your account, and for other purposes described in our privacy policy.</p>
              </div>

              <button class="btn btn-primary w-100 text-uppercase" type="submit">Register</button>

              <div class="customer-option mt-4 text-center">
                <span class="text-secondary">Have an account?</span>
                <a href="{{route('login')}}" class="btn-text js-show-register">Login to your Account</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>
{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> --}}
@endsection

@push('scripts')
<script>
  (function () {
    // Mobile: allow only digits and max 11
    const mobile = document.getElementById('mobile');
    const mobileHelp = document.getElementById('mobile-help');

    function cleanMobile() {
      const digits = mobile.value.replace(/\D/g, '').slice(0, 11);
      if (mobile.value !== digits) mobile.value = digits;
      if (digits.length > 0 && digits.length !== 11) {
        mobileHelp.textContent = 'Mobile must be exactly 11 digits.';
      } else {
        mobileHelp.textContent = '';
      }
    }
    mobile.addEventListener('input', cleanMobile);
    cleanMobile();

    // Password strength: letter + number + special + length >= 8
    const pwd = document.getElementById('password');
    const pwdHelp = document.getElementById('password-help');
    const pwdConfirm = document.getElementById('password-confirm');
    const pwdConfirmHelp = document.getElementById('password-confirm-help');

    function isStrongPassword(val) {
      const hasLetter = /[A-Za-z]/.test(val);
      const hasNumber = /\d/.test(val);
      const hasSpecial = /[^A-Za-z0-9]/.test(val);
      const isLong = val.length >= 8;
      return hasLetter && hasNumber && hasSpecial && isLong;
    }

    function updatePasswordHelp() {
      const val = pwd.value;
      if (!val) {
        pwd.classList.remove('is-valid', 'is-invalid');
        pwdHelp.textContent = '';
        pwdHelp.className = 'small mt-1';
        return;
      }
      if (isStrongPassword(val)) {
        pwd.classList.add('is-valid');
        pwd.classList.remove('is-invalid');
        pwdHelp.textContent = 'Strong password ✅';
        pwdHelp.className = 'small mt-1 text-success';
      } else {
        pwd.classList.add('is-invalid');
        pwd.classList.remove('is-valid');
        let missing = [];
        if (!/[A-Za-z]/.test(val)) missing.push('a letter');
        if (!/\d/.test(val)) missing.push('a number');
        if (!/[^A-Za-z0-9]/.test(val)) missing.push('a special character');
        if (val.length < 8) missing.push('at least 8 characters');
        pwdHelp.textContent = 'Password must include ' + missing.join(', ') + '.';
        pwdHelp.className = 'small mt-1 text-danger';
      }
      updateConfirmHelp();
    }

    function updateConfirmHelp() {
      if (!pwdConfirm.value) {
        pwdConfirm.classList.remove('is-valid', 'is-invalid');
        pwdConfirmHelp.textContent = '';
        return;
      }
      if (pwdConfirm.value === pwd.value) {
        pwdConfirm.classList.add('is-valid');
        pwdConfirm.classList.remove('is-invalid');
        pwdConfirmHelp.textContent = '';
      } else {
        pwdConfirm.classList.add('is-invalid');
        pwdConfirm.classList.remove('is-valid');
        pwdConfirmHelp.textContent = 'Passwords do not match.';
        pwdConfirmHelp.className = 'small mt-1 text-danger';
      }
    }

    pwd.addEventListener('input', updatePasswordHelp);
    pwdConfirm.addEventListener('input', updateConfirmHelp);

    // Prevent submit if invalid (client-side)
    const form = document.forms['register-form'];
    form.addEventListener('submit', function (e) {
      cleanMobile();
      updatePasswordHelp();
      updateConfirmHelp();
      const mobileValid = mobile.value.length === 11;
      const pwdValid = isStrongPassword(pwd.value);
      const confirmValid = pwdConfirm.value === pwd.value;
      if (!mobileValid || !pwdValid || !confirmValid) {
        e.preventDefault();
      }
    });
  })();
  // Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function() {
  const input = document.getElementById('password');
  const icon = this.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});

// Toggle confirm password visibility
document.querySelector('.toggle-password-confirm').addEventListener('click', function() {
  const input = document.getElementById('password-confirm');
  const icon = this.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});

</script>

@endpush