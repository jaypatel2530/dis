                        <div class="member_add" id="membersection_{{ $len }}" style="border: 1px dotted black;margin-top: 2%;padding: 2%;">
                        <div class="form-row" style="display: flow-root;">
                            <button class="btn btn--radius-2 btn--red" title="Delete this member" type="button" onclick="removeMember({{ $len }})" style="float: right;"><i class="fa fa-user-times" aria-hidden="true"></i></button>
                        </div>
                        
                        <div class="form-row">
                            <div class="name">Relation With You:*</div>
                            <div class="value">
                                <div class="input-group">
                                    <div class="rs-select2 js-select-simple select--no-search">
                                        <select name="relation_with_user[]" required>
                                            <!--<option disabled="disabled" selected="selected">Choose option</option>-->
                                            <option value="Spouse">Spouse</option>
                                            <option value="Son">Son</option>
                                            <option value="Daughter">Daughter</option>
                                            <option value="Parents">Parents</option>
                                            <option value="Brother">Brother</option>
                                            <option value="Sister">Sister</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="select-dropdown"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="name">Name*</div>
                            <div class="value">
                                <div class="row row-space">
                                    <div class="col-2">
                                        <div class="input-group-desc">
                                            <input class="input--style-5" type="text" name="first_name_m[]" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32) || (event.charCode == 39) || (event.charCode == 45)" required>
                                            <label class="label--desc">first name</label>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="input-group-desc">
                                            <input class="input--style-5" type="text" name="last_name_m[]" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32) || (event.charCode == 39) || (event.charCode == 45)" required>
                                            <label class="label--desc">last name</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Profession</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="text" name="profession_m[]">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Email</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="email" name="email_m[]">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Mobile No</div>
                            <div class="value">
                                <div class="input-group">
                                    
                                    <input class="input--style-5" maxlength="11" minlength="11" onkeypress="return isNumber(event)" type="tel" name="mobile_m[]">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Gender:*</div>
                            <div class="value">
                                <div class="input-group">
                                    <div class="rs-select2 js-select-simple select--no-search">
                                        <select name="gender_m[]" required>
                                            <!--<option disabled="disabled" selected="selected">Choose option</option>-->
                                            <option value="Male" selected="selected">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <div class="select-dropdown"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn--radius-2 btn--blue" title="Add New Member" type="button" onclick="addMember()"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Family Member</button>
                        <br>
                        <small>click this button again to add more than one family members</small><br>
                        </div>
                        <br>