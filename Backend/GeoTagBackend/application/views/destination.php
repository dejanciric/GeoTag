
<!-- Jakov Jezdic 0043/15 -->


<div class="container-fluid">
<div class="row">
   <div class="col-8">      
      <div class="jumbotron" style="width:100%;background-size:cover; background-image: url('<?php echo base_url(); ?>img/brown-gradient.png'); height:85%; overflow:auto;border-radius:5px; ">
         <div class="media" style="width: 100%" >
            <img src="<?php if ($image==null) echo base_url().'img/destination-icon.png'; else echo $image; ?>" width="180" style="margin-right:20px;border: 1px solid #3f2a14; border-radius:4px; box-shadow: 3px 3px 5px rgba(43,29,14, .3);">
            <br>
            <div class="media-body" style="overflow:auto;width:70%">
                <table>
                    <tr>
                        <td style="width: 100%">
                            <h2 class="mt-2">
                                <strong>
                                    <?php echo $dest_name.", ".$dest_country ?> 
                                </strong> 
                            </h2>
                        </td>
                        <!--
                        <td style="width: 60%">
                            <div class="progress" style="margin-left:50px;width:100%; height:20px">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style=" background-color: COLOR;width: <?php echo $review_balance_percentage;?>%">
                                  PERCENTAGE%
                                </div>
                            </div>
                        </td>
                        -->
                    </tr>
                </table>
         
               <hr>
               <div style="height:80%;width: 100%;overflow:auto;marign-bottom:10px;">
                      
                     <?php echo $all_reviews_current_destination_html; ?>
                     
               </div>
            </div>
         </div>
         <br><br>
      </div>
   </div>
   <div class="col-4">
      <div class="jumbotron" style="background-size:cover; background-image: url('<?php echo base_url(); ?>img/brown-gradient.png'); height:85%; overflow:auto;">
         <?php
            if (($this->session->userdata('user')) != NULL) {
            $user1 = $this->session->userdata('user')->status;
            }
            else
                $user1 ="guest";
         ?>
         <form action="<?php echo base_url() ?>index.php/<?php echo $user1;?>/add_review/<?php echo $dest_id;?>" method="POST"enctype="multipart/form-data">
             <input type="hidden"  id="dest_id" name="dest_id" value="<?php echo $dest_id;?>">
            <div class="form-group">
               <label for="comment">
                  <h5><strong>Write your review</strong></h5>
               </label>
               <center><textarea class="form-control" rows="10" id="comment" name="comment" cols="40"></textarea></center>
               <span><font color = "red"><?php echo form_error("comment", "<font color='red'>", "</font>"); ?></font></span>
               <div style="padding-top:2%">
                   <label for="pic" class="btn btn-light">Browse image</label>
                   <input type="file" id="pic" name="pic" style="display: none;" class="form-control-file">
                   <?php if ($message!=null) echo "<font color='red'>".$message. "</font>";?>
               </div>
               <hr>
               <input type="submit" value="Add review" class="btn btn-warning">
            </div>
         </form>
      </div>
   </div>
</div>