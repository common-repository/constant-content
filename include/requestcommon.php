<?php
$types = ConstantContent::get('content_type_list');
$prices = ConstantContent::get('request_price_list');
$countries = ConstantContent::get('country_list');
$studies = ConstantContent::get('study_list');
$categories = ConstantContent::get('category_list');
$certfications = ConstantContent::get('certification_list');
$educations = ConstantContent::get('education_list');
$experts = ConstantContent::get('expert_list');
$writers = ConstantContent::get('writers_list');
$teams = ConstantContent::get('teams_list');
?>
<h2>Order Custom Content</h2>
<div style="width: 700px;">
    <form name="newrequest" id="newrequest" method="post">
        <input type="hidden" name="request_action" value="<?= $action ?>">
        <input type="hidden" name="request" value="<?= $id ?>">
        <table style='width: 100%'>
            <tr>
                <td>Title</td>
                <td><input name="title" value="<?= $title ?>"></td>
            </tr>
            <tr>
                <td>Deadline</td>
                <td><input name="deadline" id="deadline" value="<?= $deadline ?>"></td>
            </tr>
            <tr>
                <td>Content Type</td>
                <td>
                    <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                    <select name="type">
                        <?php foreach ($types as $type_id => $typeItem) { ?>
                            <option value="<?= $type_id ?>" <?php
                            if ($typeItem == $type) {
                                echo ' selected="selected" ';
                            }
                            ?>><?= $typeItem ?></option>
                                <?php } ?>
                    </select>
                    <?php } else { ?>
                    <input value="Articles" disabled="disabled">
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td>Request Type</td>
                <td style="overflow: hidden;">
                    <select name="authors" onchange="choose_request(this)" id="chooseAuthors">
                        <option value="call_for_articles" <?php
                        if ($reqType == 'call_for_articles') {
                            echo ' selected="selected" ';
                        }
                        ?>>Call For Articles</option>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option value="targeted_request" <?php
                            if ($reqType == 'targeted_request') {
                                echo ' selected="selected" ';
                            }
                            ?>>Targeted Request</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option class="optionChild" value="targeted_request_country" <?php
                            if ($reqType == 'targeted_request_country') {
                                echo ' selected="selected" ';
                            }
                            ?>>Targeted Request: Country</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option class="optionChild" value="targeted_request_study" <?php
                            if ($reqType == 'targeted_request_study') {
                                echo ' selected="selected" ';
                            }
                            ?>>Targeted Request: Study</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option class="optionChild" value="targeted_request_certification" <?php
                            if ($reqType == 'targeted_request_certification') {
                                echo ' selected="selected" ';
                            }
                            ?>>Targeted Request: Certification</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option class="optionChild" value="targeted_request_category" <?php
                            if ($reqType == 'targeted_request_category') {
                                echo ' selected="selected" ';
                            }
                            ?>>Targeted Request: Category</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option value="expert_request" <?php
                            if ($reqType == 'expert_request') {
                                echo ' selected="selected" ';
                            }
                            ?>>Expert Request</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'legacy') { ?>
                            <option value="casting_call" <?php
                            if ($reqType == 'casting_call') {
                                echo ' selected="selected" ';
                            }
                            ?>>Casting Call</option>
                                <?php } ?>
                                <?php if ($requestClass === 'both' || $requestClass === 'pool') { ?>
                            <option value="private_team" <?php
                            if ($reqType == 'private_team') {
                                echo ' selected="selected" ';
                            }
                            ?>>Private: Team</option>
                                <?php } ?>
                        <option value="private_writer" <?php
                        if ($reqType == 'private_writer') {
                            echo ' selected="selected" ';
                        }
                        ?>>Private: Writer</option>
                    </select>
                    <select class="target_country hidden sublist" name='country' id='categories'>
                        <option value=''>Target: Country</option>
                        <?php foreach ($countries as $country) {
                            ?>
                            <option value="<?= $country['code'] ?>" <?php
                            if ($country['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?>><?= $country['name'] ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <select class="target_study hidden sublist" name='study' id='categories'>
                        <option value=''>Target: Area of Study</option>
                        <?php foreach ($studies as $study) {
                            ?>
                            <option value="<?= $study['code'] ?>" <?php
                            if ($study['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?> ><?= $study['name'] ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <select class="target_certification hidden sublist" name='certfication' id='certfication'>
                        <option value=''>Target: Certifications</option>
                        <?php foreach ($certfications as $certfication) {
                            ?>
                            <option value="<?= $certfication['code'] ?>" <?php
                            if ($certfication['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?> ><?= $certfication['name'] ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <select class="target_category hidden sublist" name='categories' id='categories'>
                        <option value=''>Target: Category</option>
                        <?php foreach ($categories as $category) {
                            ?>
                            <option value="<?= $category['code'] ?>" <?php
                            if ($category['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?> class="optionGroup"><?= $category['name'] ?></option>
                                    <?php foreach ($category['subcat'] as $subcat) { ?>
                                <option value="<?= $subcat['code'] ?>" <?php
                                if ($subcat['code'] == $raw_type) {
                                    echo ' selected="selected" ';
                                }
                                ?> class="optionChild"><?= $subcat['name'] ?></option>
                                        <?php
                                    }
                                }
                                ?>
                    </select>
                    <select class="expert hidden sublist" name='expert' id='experts'>
                        <option value=''>Expert Groups</option>
                        <?php foreach ($experts as $expert) {
                            ?>
                            <option value="<?= $expert['code'] ?>" <?php
                            if ($expert['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?> ><?= $expert['name'] ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <select class="team hidden sublist" name='team' id='team'>
                        <option value=''>Private: Team</option>
                        <?php foreach ($teams as $team) {
                            ?>
                            <option value="<?= $team['code'] ?>" <?php
                            if ($team['code'] == $raw_type) {
                                echo ' selected="selected" ';
                            }
                            ?> ><?= $team['name'] ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <select class="writers hidden sublist" name='writers[]' id='writers' multiple>
                        <option value=''>Private: Writer</option>
                        <?php foreach ($writers as $writer) {
                            ?>
                            <option value="<?= $writer['id'] ?>" <?php
                            if (in_array($writer['id'], $authors)) {
                                echo ' selected="selected" ';
                            }
                            ?> ><?= $writer['penname']; ?>(<?= $writer['documents']; ?>)</option>
                                    <?php
                                }
                                ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td><textarea name="description" value="" rows='4'><?= $description ?></textarea></td>
            </tr>
            <tr>
                <td>Subjects</td>
                <td><textarea name="subjects" value="" rows='4'><?= $subjects ?></textarea></td>
            </tr>
            <tr>
                <td>Price</td>
                <td>
                    <select name="price">
                        <?php foreach ($prices as $price_id => $price_item) { ?>
                            <option <?php
                            if ($price == $price_id) {
                                echo ' selected = "selected"';
                            }
                            ?> value="<?= $price_id ?>"><?= $price_item ?></option>
                            <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Word Count</td>
                <td><input name="wordcount" value="<?php
                    if (empty($wordcount)) {
                        echo '500';
                    } else {
                        echo $wordcount;
                    }
                    ?>"></td>
            </tr>
            <tr>
                <td># of Items</td>
                <td><input name="item_count" value="<?php
                    if (empty($item_count)) {
                        echo '1';
                    } else {
                        echo $item_count;
                    }
                    ?>"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php if ($action == 'create') { ?>
                        <input type="submit" name="submit" value="Create Request">
                    <?php } elseif ($action == 'update') { ?>
                        <input type="submit" name="submit" value="Update Request">
                    <?php } else { ?>
                        <input type="submit" name="submit" value="Submit Request">
                    <?php } ?>
                </td>
            </tr>
        </table>
    </form>
</div>
