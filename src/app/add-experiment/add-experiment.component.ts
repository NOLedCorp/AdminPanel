import { Component, OnInit } from '@angular/core';
import { AddService } from '../services/add.service';
import { AdminService } from '../services/admin.service';
import { Validators } from '@angular/forms';

@Component({
  selector: 'app-add-experiment',
  templateUrl: './add-experiment.component.html',
  styleUrls: ['./add-experiment.component.less']
})
export class AddExperimentComponent extends AddService implements OnInit {

 
  constructor(private as:AdminService) { 
    super();
  }

  ngOnInit() {
    this.addForm = this.fb.group({
      name: ['', Validators.required],
      formulae: ['', Validators.required],
      id_type: ['', Validators.required]
    });
  }

  send(){
    this.submitted = true;
    if(this.addForm.invalid){
      return;
    }
    this.as.addSolid(this.v).subscribe(x => {
      this.addForm.reset();
      this.submitted = false;
    })
  }

}
