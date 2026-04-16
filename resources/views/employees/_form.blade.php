<div class="row">
    <!-- Sidebar: Photo -->
    <div class="col-md-3">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-4">
                    @if(isset($employee) && $employee->photo)
                        <img src="{{ asset('storage/' . $employee->photo) }}" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 150px; height: 150px;">
                            <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="mb-0 text-start">
                    <label for="photo" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="bi bi-camera me-1"></i> Choose Photo
                    </label>
                    <input type="file" name="photo" id="photo" class="d-none" accept="image/*">
                    @error('photo')
                        <div class="text-danger small">Invalid image file</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Information Sections -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="mb-5">
                    <h5 class="mb-4 text-primary pb-2 border-bottom">Personal Information</h5>
                    <!-- Row 0: ID and Status -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Employee ID</label>
                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" 
                               placeholder="ID Number" value="{{ old('employee_id', $employee->employee_id ?? '') }}">
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-danger">Bundy PIN Code</label>
                        <div class="input-group">
                            <input type="password" name="web_bundy_code" id="web_bundy_code" class="form-control border-danger @error('web_bundy_code') is-invalid @enderror" 
                                   placeholder="Private PIN" value="{{ old('web_bundy_code', $employee->web_bundy_code ?? '') }}">
                            <button class="btn btn-outline-danger" type="button" id="toggleBundyPin">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('web_bundy_code')
                            <div class="invalid-feedback d-block">PIN is required (min 4 chars)</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Employment Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $employee->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $employee->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="resigned" {{ old('status', $employee->status ?? '') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                            <option value="terminated" {{ old('status', $employee->status ?? '') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                </div>

                <!-- Row 1: Title -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Title</label>
                        <select name="title" class="form-select">
                            <option value="">Select</option>
                            <option value="Mr." {{ old('title', $employee->title ?? '') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Ms." {{ old('title', $employee->title ?? '') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Mrs." {{ old('title', $employee->title ?? '') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Names -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                               placeholder="First Name" value="{{ old('first_name', $employee->first_name ?? '') }}">
                        @error('first_name')
                            <div class="invalid-feedback">First Name is required</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" 
                               placeholder="Middle Name" value="{{ old('middle_name', $employee->middle_name ?? '') }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                               placeholder="Last Name" value="{{ old('last_name', $employee->last_name ?? '') }}">
                        @error('last_name')
                            <div class="invalid-feedback">Last Name is required</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Name extension</label>
                        <input type="text" name="name_extension" class="form-control" 
                               placeholder="Name extension" value="{{ old('name_extension', $employee->name_extension ?? '') }}">
                    </div>
                </div>

                <!-- Row 3: Demographics -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Birthday</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" name="birthday" class="form-control @error('birthday') is-invalid @enderror" 
                                   value="{{ old('birthday', $employee->birthday ?? '') }}">
                        </div>
                        @error('birthday')
                            <div class="text-danger small mt-1">Birthday is required</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                            <div class="text-danger small mt-1">Gender is required</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Civil Status</label>
                        <select name="civil_status" class="form-select @error('civil_status') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="Single" {{ old('civil_status', $employee->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status', $employee->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Widowed" {{ old('civil_status', $employee->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="Separated" {{ old('civil_status', $employee->civil_status ?? '') == 'Separated' ? 'selected' : '' }}>Separated</option>
                        </select>
                        @error('civil_status')
                            <div class="text-danger small mt-1">Civil Status is required</div>
                        @enderror
                    </div>
                </div>

                <!-- Row 4: Place of Birth -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Place of Birth</label>
                    <input type="text" name="place_of_birth" class="form-control" 
                           placeholder="Place of Birth" value="{{ old('place_of_birth', $employee->place_of_birth ?? '') }}">
                </div>

                <!-- Row 5: Blood Type, Citizenship, Religion -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Blood Type</label>
                        <select name="blood_type" class="form-select">
                            <option value="">Select</option>
                            <option value="A+" {{ old('blood_type', $employee->blood_type ?? '') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_type', $employee->blood_type ?? '') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_type', $employee->blood_type ?? '') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_type', $employee->blood_type ?? '') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_type', $employee->blood_type ?? '') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_type', $employee->blood_type ?? '') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_type', $employee->blood_type ?? '') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_type', $employee->blood_type ?? '') == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Citizenship</label>
                        <select name="citizenship" class="form-select">
                            <option value="">Select</option>
                            <option value="Filipino" {{ old('citizenship', $employee->citizenship ?? '') == 'Filipino' ? 'selected' : '' }}>Filipino</option>
                            <option value="Other" {{ old('citizenship', $employee->citizenship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Religion</label>
                        <select name="religion" class="form-select">
                            <option value="">Select</option>
                            <option value="Roman Catholic" {{ old('religion', $employee->religion ?? '') == 'Roman Catholic' ? 'selected' : '' }}>Roman Catholic</option>
                            <option value="Christian" {{ old('religion', $employee->religion ?? '') == 'Christian' ? 'selected' : '' }}>Christian</option>
                            <option value="Islam" {{ old('religion', $employee->religion ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Other" {{ old('religion', $employee->religion ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Section: Employment Information -->
                <div class="border-top pt-4 mt-4">
                    <h5 class="mb-4 text-primary">Employment Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Site</label>
                            <select name="site_id" class="form-select border-primary @error('site_id') is-invalid @enderror" required>
                                <option value="">-- Select Site --</option>
                                @foreach($sites as $s)
                                    <option value="{{ $s->id }}" {{ old('site_id', $employee->site_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('site_id')
                                <div class="text-danger small mt-1">Site is required</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Classification</label>
                            <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="Probationary" {{ old('employment_type', $employee->employment_type ?? '') == 'Probationary' ? 'selected' : '' }}>Probationary</option>
                                <option value="Regular" {{ old('employment_type', $employee->employment_type ?? '') == 'Regular' ? 'selected' : '' }}>Regular</option>
                            </select>
                            @error('employment_type')
                                <div class="text-danger small mt-1">Classification is required</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Level</label>
                            <select name="classification" class="form-select @error('classification') is-invalid @enderror">
                                <option value="HR" {{ old('classification', $employee->classification ?? '') == 'HR' ? 'selected' : '' }}>HR</option>
                                <option value="Accounting" {{ old('classification', $employee->classification ?? '') == 'Accounting' ? 'selected' : '' }}>Accounting</option>
                                <option value="Marketing" {{ old('classification', $employee->classification ?? '') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                            </select>
                            @error('classification')
                                <div class="text-danger small mt-1">Level is required</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Position</label>
                            <select name="position" class="form-select @error('position') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="Customer Service Representative (CSR)" {{ old('position', $employee->position ?? '') == 'Customer Service Representative (CSR)' ? 'selected' : '' }}>Customer Service Representative (CSR)</option>
                                <option value="Technical Support Representative (TSR)" {{ old('position', $employee->position ?? '') == 'Technical Support Representative (TSR)' ? 'selected' : '' }}>Technical Support Representative (TSR)</option>
                                <option value="Outbound Sales Agent" {{ old('position', $employee->position ?? '') == 'Outbound Sales Agent' ? 'selected' : '' }}>Outbound Sales Agent</option>
                                <option value="Quality Assurance (QA) Specialist" {{ old('position', $employee->position ?? '') == 'Quality Assurance (QA) Specialist' ? 'selected' : '' }}>Quality Assurance (QA) Specialist</option>
                                <option value="Team Leader (TL)" {{ old('position', $employee->position ?? '') == 'Team Leader (TL)' ? 'selected' : '' }}>Team Leader (TL)</option>
                                <option value="Operations Manager (OM)" {{ old('position', $employee->position ?? '') == 'Operations Manager (OM)' ? 'selected' : '' }}>Operations Manager (OM)</option>
                                <option value="Trainer" {{ old('position', $employee->position ?? '') == 'Trainer' ? 'selected' : '' }}>Trainer</option>
                                <option value="Subject Matter Expert (SME)" {{ old('position', $employee->position ?? '') == 'Subject Matter Expert (SME)' ? 'selected' : '' }}>Subject Matter Expert (SME)</option>
                                @if(isset($employee) && !in_array($employee->position, [
                                    'Customer Service Representative (CSR)', 
                                    'Technical Support Representative (TSR)', 
                                    'Outbound Sales Agent', 
                                    'Quality Assurance (QA) Specialist',
                                    'Team Leader (TL)',
                                    'Operations Manager (OM)',
                                    'Trainer',
                                    'Subject Matter Expert (SME)'
                                ]))
                                    <option value="{{ $employee->position }}" selected>{{ $employee->position }}</option>
                                @endif
                            </select>
                            @error('position')
                                <div class="text-danger small mt-1">Position is required</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Daily Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="daily_rate" class="form-control @error('daily_rate') is-invalid @enderror" 
                                       value="{{ old('daily_rate', $employee->daily_rate ?? '') }}" placeholder="0.00">
                            </div>
                            @error('daily_rate')
                                <div class="text-danger small mt-1">Daily rate is required</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Employed</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="date_employed" class="form-control @error('date_employed') is-invalid @enderror" 
                                       value="{{ old('date_employed', $employee->date_employed ?? '') }}">
                            </div>
                            @error('date_employed')
                                <div class="text-danger small mt-1">Date Employed is required</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tax Code</label>
                            <select name="tax_code" class="form-select @error('tax_code') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="S/ME" {{ old('tax_code', $employee->tax_code ?? '') == 'S/ME' ? 'selected' : '' }}>S/ME</option>
                                <option value="S/ME1" {{ old('tax_code', $employee->tax_code ?? '') == 'S/ME1' ? 'selected' : '' }}>S/ME1</option>
                            </select>
                            @error('tax_code')
                                <div class="text-danger small mt-1">Tax Code is required</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pay Type</label>
                            <select name="pay_type" class="form-select @error('pay_type') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="Monthly" {{ old('pay_type', $employee->pay_type ?? '') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Weekly" {{ old('pay_type', $employee->pay_type ?? '') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Daily" {{ old('pay_type', $employee->pay_type ?? '') == 'Daily' ? 'selected' : '' }}>Daily</option>
                            </select>
                            @error('pay_type')
                                <div class="text-danger small mt-1">Pay Type is required</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Payroll Group</label>
                            <select name="payroll_group_id" class="form-select border-primary @error('payroll_group_id') is-invalid @enderror" required>
                                <option value="">-- Assign Group --</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}" {{ old('payroll_group_id', $employee->payroll_group_id ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                @endforeach
                            </select>
                            @error('payroll_group_id')
                                <div class="text-danger small mt-1">Payroll Group is required</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Report to</label>
                            <input type="text" name="report_to" class="form-control bg-light" 
                                   placeholder="Report to" value="{{ old('report_to', $employee->report_to ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Section: Account Information -->
                <div class="border-top pt-5 mt-5">
                    <h5 class="mb-4 text-primary pb-2 border-bottom" style="border-color: #198754 !important;">Account Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bank</label>
                            <select name="bank_name" class="form-select">
                                <option value="">Select</option>
                                <option value="BDO" {{ old('bank_name', $employee->bank_name ?? '') == 'BDO' ? 'selected' : '' }}>BDO</option>
                                <option value="BPI" {{ old('bank_name', $employee->bank_name ?? '') == 'BPI' ? 'selected' : '' }}>BPI</option>
                                <option value="Metrobank" {{ old('bank_name', $employee->bank_name ?? '') == 'Metrobank' ? 'selected' : '' }}>Metrobank</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Account No.</label>
                            <input type="text" name="account_no" class="form-control" 
                                   placeholder="Account No." value="{{ old('account_no', $employee->account_no ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">TIN</label>
                            <input type="text" name="tin_no" class="form-control" 
                                   placeholder="TIN" value="{{ old('tin_no', $employee->tin_no ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SSS No.</label>
                            <input type="text" name="sss_no" class="form-control" 
                                   placeholder="SSS No." value="{{ old('sss_no', $employee->sss_no ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pagibig No.</label>
                            <input type="text" name="pagibig_no" class="form-control" 
                                   placeholder="Pagibig No." value="{{ old('pagibig_no', $employee->pagibig_no ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Philhealth</label>
                            <input type="text" name="philhealth_no" class="form-control" 
                                   placeholder="Philhealth No." value="{{ old('philhealth_no', $employee->philhealth_no ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Section: Contact Details -->
                <div class="border-top pt-5 mt-5">
                    <h5 class="mb-4 text-primary pb-2 border-bottom" style="border-color: #fd7e14 !important;">Contact Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mobile No. 1</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">+63</span>
                                <input type="text" name="mobile_no_1" class="form-control" 
                                       placeholder="9xxxxxxxxx" value="{{ old('mobile_no_1', $employee->mobile_no_1 ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mobile No. 2</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">+63</span>
                                <input type="text" name="mobile_no_2" class="form-control" 
                                       placeholder="9xxxxxxxxx" value="{{ old('mobile_no_2', $employee->mobile_no_2 ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tel No. 1</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="tel_no_1" class="form-control" 
                                       placeholder="xxx-xxxx" value="{{ old('tel_no_1', $employee->tel_no_1 ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tel No. 2</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="tel_no_2" class="form-control" 
                                       placeholder="xxx-xxxx" value="{{ old('tel_no_2', $employee->tel_no_2 ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-at"></i></span>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="sample@email.ph" value="{{ old('email', $employee->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">Valid email is required</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Facebook</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-primary"><i class="bi bi-facebook"></i></span>
                                <input type="text" name="facebook_url" class="form-control" 
                                       placeholder="http://www.facebook.com/sample" value="{{ old('facebook_url', $employee->facebook_url ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Twitter</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-info"><i class="bi bi-twitter"></i></span>
                                <input type="text" name="twitter_url" class="form-control" 
                                       placeholder="@twittersample" value="{{ old('twitter_url', $employee->twitter_url ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-danger"><i class="bi bi-instagram"></i></span>
                                <input type="text" name="instagram_url" class="form-control" 
                                       placeholder="@instagramsample" value="{{ old('instagram_url', $employee->instagram_url ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Address -->
                <div class="border-top pt-5 mt-5">
                    <h5 class="mb-4 text-primary pb-2 border-bottom">Address</h5>
                    
                    <!-- Permanent Address -->
                    <div class="mb-4">
                        <label class="text-danger fw-bold small mb-2">&raquo; Permanent Address</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Brgy./Street/Subdivision</label>
                                <textarea name="permanent_address_brgy" class="form-control @error('permanent_address_brgy') is-invalid @enderror" rows="3" placeholder="Brgy./Street/Subdivision">{{ old('permanent_address_brgy', $employee->permanent_address_brgy ?? '') }}</textarea>
                                @error('permanent_address_brgy')
                                    <div class="invalid-feedback">Brgy./Street/Subdivision is required</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Province</label>
                                <select name="permanent_address_province" class="form-select @error('permanent_address_province') is-invalid @enderror">
                                    <option value="">Select</option>
                                    <option value="Leyte" {{ old('permanent_address_province', $employee->permanent_address_province ?? '') == 'Leyte' ? 'selected' : '' }}>Leyte</option>
                                    <option value="Southern Leyte" {{ old('permanent_address_province', $employee->permanent_address_province ?? '') == 'Southern Leyte' ? 'selected' : '' }}>Southern Leyte</option>
                                    <option value="Samar" {{ old('permanent_address_province', $employee->permanent_address_province ?? '') == 'Samar' ? 'selected' : '' }}>Samar</option>
                                </select>
                                @error('permanent_address_province')
                                    <div class="text-danger small mt-1">Province is required</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="same_address_check" onclick="copyAddress()">
                            <label class="form-check-label fw-bold small" for="same_address_check">
                                check if permanent address and present address are the same
                            </label>
                        </div>
                    </div>

                    <!-- Present Address -->
                    <div class="mb-4">
                        <label class="text-danger fw-bold small mb-2">&raquo; Present Address</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Brgy./Street/Subdivision</label>
                                <textarea name="present_address_brgy" id="present_address_brgy" class="form-control @error('present_address_brgy') is-invalid @enderror" rows="3" placeholder="Brgy./Street/Subdivision">{{ old('present_address_brgy', $employee->present_address_brgy ?? '') }}</textarea>
                                @error('present_address_brgy')
                                    <div class="invalid-feedback">Brgy./Street/Subdivision is required</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Province</label>
                                <select name="present_address_province" id="present_address_province" class="form-select @error('present_address_province') is-invalid @enderror">
                                    <option value="">Select</option>
                                    <option value="Leyte" {{ old('present_address_province', $employee->present_address_province ?? '') == 'Leyte' ? 'selected' : '' }}>Leyte</option>
                                    <option value="Southern Leyte" {{ old('present_address_province', $employee->present_address_province ?? '') == 'Southern Leyte' ? 'selected' : '' }}>Southern Leyte</option>
                                    <option value="Samar" {{ old('present_address_province', $employee->present_address_province ?? '') == 'Samar' ? 'selected' : '' }}>Samar</option>
                                </select>
                                @error('present_address_province')
                                    <div class="text-danger small mt-1">Province is required</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Other Information -->
                <div class="border-top pt-4 mt-4" style="border-top: 3px solid #dc3545 !important;">
                    <h5 class="mb-4 text-primary">Other Information</h5>
                    <div class="mb-3">
                        <textarea name="other_information" class="form-control" rows="4" placeholder="Enter any other relevant information...">{{ old('other_information', $employee->other_information ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Employee</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleBundyPin');
        const pinInput = document.getElementById('web_bundy_code');
        
        if (toggleBtn && pinInput) {
            toggleBtn.addEventListener('click', function() {
                const type = pinInput.getAttribute('type') === 'password' ? 'text' : 'password';
                pinInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }
            });
        }
    });

    function copyAddress() {
        if (document.getElementById('same_address_check').checked) {
            document.getElementById('present_address_brgy').value = document.getElementsByName('permanent_address_brgy')[0].value;
            document.getElementById('present_address_province').value = document.getElementsByName('permanent_address_province')[0].value;
        }
    }
</script>
@endpush