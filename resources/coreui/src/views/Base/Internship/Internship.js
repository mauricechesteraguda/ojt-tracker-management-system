import React, { Component } from 'react';
import {
  Alert,
  Row,
  Col,
  Card,
  CardHeader,
  CardBody,
  Table,
  Button,
  Modal, ModalHeader, ModalBody, ModalFooter,
  Form,
  FormGroup,
  FormText,
  Label,
  Input,
  Badge

} from 'reactstrap';
import axios from 'axios';
// import Select from 'react-select';
import Paginations from "react-js-pagination";
import Description from "../Internship/Description";
import Requirement from "../Internship/Requirement";
import Report from "../Internship/Report";

import Can from '../../../Can';



class Internship extends Component {
  constructor(props) {
    super(props)
    this.state = {
      companies: [],
      internships: [],
      campuses:[],
      campus:'',
      schoolyears:[],
      schoolyear:'',
      semesters:[],
      semester:'',
      colleges:[],
      college:'',
      courses:[],
      course:'',

      print_form: false,
      add_form: false,
      id: '',
      user_id: window.current_user_id,
      company_id: '',
      company_name: '',
      start_date: '',
      end_date: '',
      representative: '',
      student_position: '',
      is_approved: 0,
      status: '',
      date_visited:'',
      comment: '',
      updated_by: window.current_user_id,

      is_add_process_type: true,
      save_button_is_disabled: true,
      update_button_is_disabled: true,
      is_detail_page: false,

      has_alert_hidden: true,
      alert_type: 'danger',
      alert_message: '',

      active_page:1,
      items_count_per_page:1,
      total_items_count:1,
      current_index:'',

      search_loading: false,
      search_message: '',
      search_alert_type: 'primary',
      search_value:''
      
    }
    this.toggle_print_form = this.toggle_print_form.bind(this);
    this.handle_input_change_print = this.handle_input_change_print.bind(this);
    this.print_item = this.print_item.bind(this);
    this.check_print_inputs = this.check_print_inputs.bind(this);
    this.toggle_add_form = this.toggle_add_form.bind(this);
    this.handle_input_change = this.handle_input_change.bind(this);
    this.save_item = this.save_item.bind(this);
    this.update_item = this.update_item.bind(this);
    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
    this.get_companies = this.get_companies.bind(this);
    this.go_back = this.go_back.bind(this);
    this.toggle_detail_page = this.toggle_detail_page.bind(this);
    this.get_campuses = this.get_campuses.bind(this);
    this.get_schoolyears = this.get_schoolyears.bind(this);
    this.get_semesters = this.get_semesters.bind(this);
    this.get_colleges = this.get_colleges.bind(this);
    this.get_courses = this.get_courses.bind(this);
    
    this.disable_print_button = this.disable_print_button.bind(this);
    this.enable_print_button = this.enable_print_button.bind(this);
  
  }

  get_courses(college_code){
    var self = this;
    axios.get('/api/internships/courses/'+college_code)
        .then(res => {
          if (res.status == 200) {
            
              self.setState({courses:res.data})
            
          }

        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
  }

  get_colleges(){
    var self = this;
    axios.get('/api/internships/colleges')
        .then(res => {
          if (res.status == 200) {
            
              self.setState({colleges:res.data})
            
          }

        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
  }

  get_semesters(){
    var self = this;
    axios.get('/api/internships/semesters')
        .then(res => {
          if (res.status == 200) {
            
              self.setState({semesters:res.data})
            
          }

        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
  }

  get_schoolyears(){
    var self = this;
    axios.get('/api/internships/schoolyears')
        .then(res => {
          if (res.status == 200) {
            
              self.setState({schoolyears:res.data})
            
          }

        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
  }

  get_campuses(){
    var self = this;
    axios.get('/api/internships/campuses')
        .then(res => {
          if (res.status == 200) {
            
              self.setState({campuses:res.data})
            
          }

        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
  }

  disable_print_button(){
    this.setState(
      {
        print_button_is_disabled: true,
      }
    ) 
  }
  enable_print_button(){

   
      this.setState(
        {
          print_button_is_disabled: false,
        })
       
   
    
  }

  go_back(){
    if (this.state.is_detail_page == true) {
      this.setState({is_detail_page:false})
    }
    
  }

  disable_buttons(){
    this.setState(
      {
        save_button_is_disabled: true,
        update_button_is_disabled: true,
      }
    ) 
  }
  enable_button(){

    if (this.state.is_add_process_type) {
      this.setState(
        {
          save_button_is_disabled: false,
          update_button_is_disabled: true,
        }
      ) 
    }else{
      this.setState(
        {
          save_button_is_disabled: true,
          update_button_is_disabled: false,
        }
      ) 
    }
    
  }

  check_inputs() {
    setTimeout(() => {
      if (this.state.user_id == '' || this.state.company_id == '') {
        console.log('Incomplete form values!')
        this.disable_buttons()
      } else {
        this.enable_button()
      }
    }, 300);
  }

  check_print_inputs() {
    setTimeout(() => {
      if (this.state.campus == '' || this.state.schoolyear == '' || this.state.semester == '' || this.state.college == '' || this.state.course == '') {
        console.log('Incomplete print form values!')
        this.disable_print_button()
      } else {
        this.enable_print_button()
      }
    }, 300);

  }

  delete_item(i) {
    var self = this;
    axios.delete('api/internships/' + this.state.internships[i].id)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.setState({
          alert_message: 'Delete Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })

      })
      .catch(function (error) {
        console.log(error);
        alert('Deletion Failed. Contact your System Administrator')
      });
  }

  update_item(e) {
    e.preventDefault();

    var self = this;
    let payload = {
      start_date: this.state.start_date,
      end_date: this.state.end_date,
      representative: this.state.representative,
      student_position: this.state.student_position,
      is_approved: this.state.is_approved,
      status: this.state.status,
      comment: this.state.comment,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/internships/' + this.state.id, payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form()
        self.setState({
          alert_message: 'Update Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })
      })
      .catch(function (error) {
        console.log(error);
        alert('Update Failed. Contact your System Administrator')
      });

  }

  load_item(i) {
    this.toggle_add_form();
    this.setState({
      id: this.state.internships[i].id,
      user_id: this.state.internships[i].user.id,
      company_id: this.state.internships[i].company.id,
      company_name: (this.state.internships[i].company.name+ ' - '+ this.state.internships[i].company.address+ ', '+this.state.internships[i].company.city+ ', '+this.state.internships[i].company.country),
      start_date: this.state.internships[i].start_date,
      end_date: this.state.internships[i].end_date,
      representative: this.state.internships[i].representative,
      student_position: this.state.internships[i].student_position,
      is_approved: this.state.internships[i].is_approved,
      date_visited: this.state.internships[i].date_visited,
      status: this.state.internships[i].status,
      comment: this.state.internships[i].comment,
      updated_by: this.state.internships[i].updated_by,

      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      user_id: window.current_user_id,
      company_id: this.state.company_id,
      start_date: this.state.start_date,
      end_date: this.state.end_date,
      representative: this.state.representative,
      student_position: this.state.student_position,
      is_approved: this.state.is_approved,
      status: this.state.status,
      comment: this.state.comment,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/internships', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
        self.setState({
          alert_message: 'Creation Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Internship already')) {
          self.setState({
            alert_message: error.response.data.message,
            alert_type: 'danger',
            has_alert_hidden: false,
          })
        }else{
          alert('Save Failed. Contact your System Administrator')
        }
        

      });

  }

  print_item(e) {
    e.preventDefault();
    
    var self = this;
    
    const form_data = new FormData();
    form_data.append('campus',self.state.campus);
    form_data.append('schoolyear',self.state.schoolyear);
    form_data.append('semester',self.state.semester);
    form_data.append('college',self.state.college);
    form_data.append('course',self.state.course);

    
    axios.post('/internships/print-pdf', form_data,{
      responseType: 'arraybuffer',
      headers:{
        'Content-Type':'multipart/form-data',
        'Accept': 'application/pdf'
      }
    })
      .then(function (response) {
        console.log(response);

        const url = window.URL.createObjectURL(new Blob([response.data],{type:"application/pdf"}));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'file.pdf'); //or any other extension
        document.body.appendChild(link);
        link.click();

        self.toggle_print_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message) {
          self.setState({
            alert_message: error.response.data.message,
            alert_type: 'danger',
            has_alert_hidden: false,
          })
        }else{
          alert('Print Failed. Contact your System Administrator')
        }
        

      });

  }

  handle_search_input_change(e) {
    // clear alert status
    var self = this;
    self.setState({
     search_message: '',
     search_alert_type: 'primary',
     has_alert_hidden: true,
     search_value: e.target.value
   })
   var value = 'xxxxxxxxxx';
   if(e.target.value){
    value = e.target.value
   
   axios.get('/api/internships/search/'+value)
      .then(function (response) {
        console.log(response);
        self.store_data_to_state(response.data)
      })
      .catch(function (error) {
        console.log(error);
        alert('Search Failed. Contact your System Administrator.')
      });
  
    }else{
      this.get_data()
    }
  }

  handle_input_change(e) {
     // clear alert status
    this.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    if (typeof e.target != undefined) {

      this.setState({ [e.target.name]: e.target.value });

      this.check_inputs();

    }

  }

  handle_input_change_print(e) {
    // clear alert status
    this.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    if (typeof e.target != undefined && e.target != null) {

      this.setState({ [e.target.name]: e.target.value });

      this.check_print_inputs();

      if (e.target.name == 'college') {
        var college_code = e.target.value;
        this.get_courses(college_code);
      }
    }
    
  



 }

  toggle_print_form() {
    this.setState({
      campus: '',
      schoolyear:'',
      semester:'',
      college:'',
      course:'',
      user_id: window.current_user_id,
      
      print_form: !this.state.print_form,
      print_button_is_disabled:true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }

  toggle_add_form() {
    this.setState({
      id: '',
      user_id: window.current_user_id,
      company_id: '',
      start_date: '',
      end_date: '',
      representative: '',
      student_position: '',
      is_approved: 0,
      date_visited: '',
      status: '',
      comment: '',
      updated_by: window.current_user_id,

      update_button_is_disabled: true,
      add_form: !this.state.add_form,
      is_add_process_type: true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  toggle_detail_page(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        is_detail_page: true,
        current_index:i,
        id: this.state.internships[i].id,
        user_name: this.state.internships[i].user.first_name + ' ' + this.state.internships[i].user.last_name,
        company_name: (this.state.internships[i].company.name+ ' - '+ this.state.internships[i].company.address+ ', '+this.state.internships[i].company.city+ ', '+this.state.internships[i].company.province+ ', '+this.state.internships[i].company.country),
        start_date: this.state.internships[i].start_date,
        end_date: this.state.internships[i].end_date,
        representative: this.state.internships[i].representative,
        student_position: this.state.internships[i].student_position,
        is_approved: this.state.internships[i].is_approved,
        date_visited: this.state.internships[i].date_visited,
        status: this.state.internships[i].status,
        comment: this.state.internships[i].comment,
        updated_by: this.state.internships[i].updated_by,
      });
    }else{
      this.setState({
        is_detail_page: false,
      });
    }
    
  }

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      internships: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/internships/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }
  
  get_companies() {
    // if (this.state.is_add_process_type) {
      var self = this;
      axios.get('/api/companies/all')
        .then(res => {
          self.setState({companies:res.data.data})
        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
    // }

  }

  componentDidMount() {

    this.get_data()
    this.get_companies()
    this.get_campuses()
    this.get_schoolyears()
    this.get_semesters()
    this.get_colleges()

  }

  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      <div className="animated fadeIn">
        <Row hidden={this.state.is_detail_page}>
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Internship
                  </Col>
                  <Col xs="4" lg="4">
                  
                  <Input
                          type="text"
                          value={this.state.search_value}
                          
                          name="search"
                          placeholder="Search..."
                          onChange={this.handle_search_input_change}
                          />
                    </Col>
                    
                
                
                  <Col xs="4" lg="4">

                  <Can
                      role={user.role}
                      perform="internship:add"
                      yes={() => <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>}
                      no={() => <div></div>}
                      />
                        <Button className="float-lg-right" color="danger" onClick={this.toggle_print_form}><i className="fa fa-print"></i> Print</Button>
                       
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
              <Modal isOpen={this.state.print_form} toggle={this.toggle_print_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_print_form}>Print Masterlist</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                            <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="campus">Campus</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.campus} onChange={this.handle_input_change_print} type="select" name="campus" id="campus">
                                  <option value=""></option>
                                  {this.state.campuses.map((data, i) => {
                                      return (
                                        <option key={i} value={data.code}>{data.code} - {data.description}</option>
                                        
                                      )
                                    })}
                                      

                                    </Input>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="schoolyear">Academic Year</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.schoolyear} onChange={this.handle_input_change_print} type="select" name="schoolyear" id="schoolyear">
                                  <option value=""></option>
                                  {this.state.schoolyears.map((data, i) => {
                                      return (
                                        <option key={i} value={data}>{data}</option>
                                        
                                      )
                                    })}
                                      

                                    </Input>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="semester">Semester</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.semester} onChange={this.handle_input_change_print} type="select" name="semester" id="semester">
                                  <option value=""></option>
                                  {this.state.semesters.map((data, i) => {
                                      return (
                                        <option key={i} value={data}>{data}</option>
                                        
                                      )
                                    })}
                                      

                                    </Input>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="college">College</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.college} onChange={this.handle_input_change_print} type="select" name="college" id="college">
                                  <option value=""></option>
                                  {this.state.colleges.map((data, i) => {
                                      return (
                                        <option key={i} value={data.code}>{data.code} - {data.description}</option>
                                        
                                      )
                                    })}
                                      

                                    </Input>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="course">Course</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.course} onChange={this.handle_input_change_print} type="select" name="course" id="course">
                                  <option value=""></option>
                                  {this.state.courses.map((data, i) => {
                                      return (
                                        <option key={i} value={data.code}>{data.code} - {data.description}</option>
                                        
                                      )
                                    })}
                                      

                                    </Input>
                                </Col>
                              </FormGroup>
                              
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button type='submit' disabled={this.state.print_button_is_disabled} color="primary" onClick={this.print_item}><i className="fa fa-print"></i> Print</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_print_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>


                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} Internship</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                            <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="company_id">Company</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input hidden={!this.state.is_add_process_type} value={this.state.company_id} onChange={this.handle_input_change} type="select" name="company_id" id="company_id">
                                    <option></option>
                                    {this.state.companies.map((data, i) => {
                                      return (
                                        <option key={i} value={data.id}>{data.name} - {data.address}, {data.city}, {data.country} </option>
                                        
                                      )
                                    })}

                                  </Input>
  <Label hidden={this.state.is_add_process_type} htmlFor="company_id">{this.state.company_name}</Label>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="start_date">Start Date</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.start_date} onChange={this.handle_input_change} type="date" id="start_date" name="start_date" placeholder="Text" />
                                  <FormText color="muted">Input Starting Date</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="end_date">End Date</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.end_date} onChange={this.handle_input_change} type="date" id="end_date" name="end_date" placeholder="Text" />
                                  <FormText color="muted">Input End Date</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="representative">Representative</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.representative} onChange={this.handle_input_change} type="text" id="representative" name="representative" placeholder="Text" />
                                  <FormText color="muted">Name of the representative</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="student_position">Postion</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.student_position} onChange={this.handle_input_change} type="text" name="student_position" id="student_position"/>
                                  
                                </Col>
                              </FormGroup>

                              <Can
                                role={user.role}
                                perform="internship:approve"
                                yes={() => <FormGroup hidden={this.state.is_add_process_type} row>
                                <Col md="3">
                                  <Label htmlFor="is_approved">Approve</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.is_approved} onChange={this.handle_input_change} type="select" name="is_approved" id="is_approved">
                                  <option value=""></option>
                                  <option value="0">No</option>
                                  <option value="1">Yes</option>
                                      

                                    </Input>
                                </Col>
                              </FormGroup>}
                                no={() => <div></div>}
                                />
                              
                              
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="comment">Comment</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.comment} onChange={this.handle_input_change} type="text" id="comment" name="comment" placeholder="Comment" />
                                  
                                </Col>
                              </FormGroup>
                              
                              <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button type='submit' hidden={!this.state.is_add_process_type} disabled={this.state.save_button_is_disabled} color="primary" onClick={this.save_item}><i className="fa fa-save"></i> Save</Button>{' '}
                    <Button type='submit' hidden={this.state.is_add_process_type} disabled={this.state.update_button_is_disabled} color="primary" onClick={this.update_item}><i className="fa fa-edit"></i>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_add_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Student</th>
                      <th>Company</th>
                      <th>Start Date</th>
                      <th>Representative</th>
                      <th>Position</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.internships.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.user.last_name + ', ' + data.user.first_name}</td>
                          <td>{data.company.name}</td>
                          <td>{data.start_date}</td>
                          <td>{data.representative}</td>
                          <td>{data.student_position}</td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_page(i)}><i className="fa fa-book"></i> Details</Button>
                            <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>

                            <Can
                                role={user.role}
                                perform="internship:delete"
                                yes={() => <Button size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>}
                                no={() => <div></div>}
                                />
                            
                          </td>
                        </tr>
                      )
                    })}


                  </tbody>
                </Table>
                <Paginations
                    activePage={this.state.active_page}
                    itemsCountPerPage={this.state.items_count_per_page}
                    totalItemsCount={this.state.total_items_count}
                    pageRangeDisplayed={5}
                    onChange={this.handle_page_change}
                />
                
              </CardBody>
            </Card>
          </Col>

        </Row>


        
        <Row hidden={!this.state.is_detail_page}>
          <Col xs="12" lg="6">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Internship Detail
                  </Col>
                  <Col xs="4" lg="4">
                  
                  </Col>
                  <Col xs="4" lg="4">
                        
                        
                        <Button className="float-right" color="primary" onClick={this.go_back}><i className="fa fa-hand-o-left"></i> Back</Button>
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>        
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Student Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label>{this.state.user_name}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="company">Company</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.company_name}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="start_date">Start Date</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.start_date}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="end_date">End Date</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.end_date}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="representative">Representative</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.representative}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="student_position">Position</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.student_position}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="is_approved">Approved?</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label><Badge color={this.state.is_approved ? 'success':'danger'} >{this.state.is_approved ? 'approved':'pending'}</Badge></Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="date_visited">Visited</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label><Badge color={this.state.date_visited ? 'success':'danger'} >{this.state.date_visited ? 'visited':'not yet'}</Badge></Label>
                                
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="date_visited">Date Visited</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.date_visited}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="comment">Comments</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.comment}</Label>
                                </Col>
                              </FormGroup>
                              
                            </Form>

                          </CardBody>
                        
            </Card>
            
          </Col>

          <Requirement is_hidden={!this.state.is_detail_page} internship_id={this.state.id} is_approved={(this.state.is_approved==1?true:false)}></Requirement>
          

        </Row>
        
        <Description is_hidden={!this.state.is_detail_page} internship_id={this.state.id} is_approved={(this.state.is_approved==1?true:false)}></Description>

        <Report is_hidden={!this.state.is_detail_page} internship_id={this.state.id} is_approved={(this.state.is_approved==1?true:false)}></Report>
        
      </div>

    )
  }
}

export default Internship;
