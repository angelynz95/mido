package com.cia.mido;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.facebook.login.LoginResult;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;


/**
 * Created by LENOVO on 9/18/2016.
 */
public class EmployeeSearchActivity extends AppCompatActivity implements View.OnClickListener {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_employee_search);

        Button searchbtn = (Button) findViewById(R.id.searchJobButton);
        searchbtn.setOnClickListener(this); // calling onClick() method
    }


    @Override
    public void onClick(View v) {
        switch (v.getId()) {

            case R.id.searchJobButton:
                try {
                    String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.searchEmployeeUrl);
                    JSONObject request = new JSONObject();

                    Client client = new Client(url, request, "GET");
                    client.execute();

                    LinearLayout layout = (LinearLayout) findViewById(R.id.searchJobLayout);
                    setContentView(layout);

                    JSONObject response;
                    do {
                        Thread.sleep(1000);
                        response = client.getResponse();

                        String name = response.get("name").toString();
                        String link  = response.get("link").toString();
                        String job  = response.get("job").toString();
                        String bio = response.get("bio").toString();

                        String result = name + "\n" + link + "\n" + job + "\n" + bio;
                        TextView textView = (TextView) layout.findViewById(R.id.searchEmployeeResult);
                        textView.setText(result);

                    } while (response == null);

                } catch (Exception e) {
                    e.printStackTrace();
                }

            default:
                break;
        }

    }
}
