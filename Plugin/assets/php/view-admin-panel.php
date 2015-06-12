<div class="wrap">
    <h2>Mailify - Create Form <a href="?page=mailify-create" class="add-new-h2">Back</a></h2>
    <div>
        <form method="get">
            <p class="search-box">
                <label class="screen-reader-text" for="mailify-search-form">Search Forms:</label>
                <input type="search" id="mailify-search-form" name="s" value="">
                <input type="submit" id="search-submit" class="button" value="Search Forms">
            </p>
        </form>
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="formname">Form Name</label></th>
                        <td><input name="formname" type="text" id="formname" value="<?php echo $_GET['createForm']; ?>" aria-describedby="form-name" class="regular-text"></td>
                        <p class="description" id="form-name">Give your form a name and ID.</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="formdescription">Form Description</label></th>
                        <td><input name="formdescription" type="text" id="formdescription" aria-describedby="form-description" value="" class="regular-text">
                        <p class="description" id="form-description">In a few words, explain what this form is about.</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="formsendto">Form Send To</label></th>
                        <td><input name="formsendto" type="text" id="formsendto" aria-describedby="form-send" value="" class="regular-text">
                        <p class="description" id="form-send">The email address of the receiver of the email. (Leave blank for stock)</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="formforwardto">Form Page Forward</label></th>
                        <td><input name="formforwardto" type="text" id="formforwardto" aria-describedby="form-forward" value="" class="regular-text">
                        <p class="description" id="form-forward">Enter page address to forward to. (Leave blank for stock)</p></td>
                    </tr> 
                    <tr>
                        <th scope="row"><label for="formcode">Form Code</label></th>
                        <td><div id="form_creator" class="half">
                            <select id="objects" onchange=" fc.switchEle(); " name="objects">
                                <option value="text">text</option>
                                <option value="textbox">textbox</option>
                                <option value="number">number</option>
                                <option value="email">email</option>
                                <option value="textarea">textarea</option>
                                <option value="checkbox">checkbox</option>
                                <option value="dropbox">dropbox</option>
                                <option value="recaptcha">reCaptcha</option>
                                <option value="submit">submit</option>
                                <option value="hidden">hidden</option>
                            </select>
                            <div id="plchlder-text"><input type="text" placeholder="label" id="label" name="label" /></div>
                            <input type="checkbox" checked="false" id="required" name="required" value="Required?" />
                            <input type="button" value="create" onclick=" fc.createObj() "/>
                            <input type="button" value="export" onclick="fc.exportObj();" />
                            <p class="description" id="form-code">Choose form parts.</p>
                            <textarea name="formcode" id="formcode" aria-describedby="form-code" style="width:100%; min-height:300px;" class="regular-text"></textarea>
                            <p class="description" id="form-code">The JSON for the form.</p>
                        </div>
                        <div id="form_creator_container" class="half"></div>
                        <script>
                            fc.init();
                        </script>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td><input name="submit" type="submit" id="submit" value="Create" /></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>